<?php
/**
 * Zero-SSH GitHub Deployer (ZIP download → copy → optional prune + backup)
 * Works on shared hosting with PHP 7.4+ and ZipArchive. No shell access required.
 * Repos configured for user "rezaeesjd":
 *  - default: bokun-bookings-management (branch main)
 *  - alt via ?repo=github-updater: github-updater (branch main)
 *
 * Security:
 *  - Strong shared secret (HEADER/POST/GET supported).
 *  - Consider adding HTTP Basic Auth or IP allowlist in .htaccess for extra protection.
 */

/* ===================== CONFIG (EDIT THESE) ===================== */
const SECRET_TOKEN = 'deploytest_7uF9xK2aT6pR4qL1mZ8vW3nB0sC5eH9jP7rF6tY2uI4oN8wE3aD5gQ1lV7';
const DEFAULT_OWNER  = 'rezaeesjd';
const DEFAULT_REPO   = 'bokun-bookings-management';
const DEFAULT_REF    = 'main';     // branch, tag, or full commit SHA

// Additional repos you can target with ?repo=key
const ALT_REPO_MAP   = [
  'github-updater' => ['owner' => 'rezaeesjd', 'repo' => 'github-updater', 'ref' => 'main'],
];

// Deployment options
const SITE_ROOT      = __DIR__;    // where the website lives (this file’s directory)
const KEEP_BACKUP    = true;       // keep backup of overwritten/removed items
const BACKUP_DIR     = 'backups';  // folder under SITE_ROOT for backups
const PRUNE_REMOVED  = true;       // delete files/dirs no longer in repo
const TIMEOUT_SEC    = 300;        // network timeout

// Exclusions: relative paths under SITE_ROOT that should never be touched
const EXCLUDES       = [
  'deploy.php',     // keep this script safe
  '.git', '.github',
  BACKUP_DIR,       // keep backups
  // Add your local-only folders here (examples):
  // 'uploads', 'cache', 'env'
];

// Private repos only: fine-grained GitHub token with contents:read
const GITHUB_TOKEN   = ''; // leave empty if both repos are public
/* ================== END CONFIG (DON'T EDIT BELOW) ================== */

// ---------- Access control: token via Header, POST, or GET ----------
$givenToken = $_SERVER['HTTP_X_DEPLOY_TOKEN'] ?? ($_POST['token'] ?? ($_GET['token'] ?? ''));
if (!is_string($givenToken) || $givenToken !== SECRET_TOKEN) {
  http_response_code(403);
  exit('Forbidden');
}

// ---------- Choose repo/ref ----------
$reqRepoKey = isset($_GET['repo']) ? trim($_GET['repo']) : (isset($_POST['repo']) ? trim($_POST['repo']) : '');
$selected   = ($reqRepoKey && isset(ALT_REPO_MAP[$reqRepoKey]))
  ? ALT_REPO_MAP[$reqRepoKey]
  : ['owner' => DEFAULT_OWNER, 'repo' => DEFAULT_REPO, 'ref' => DEFAULT_REF];

$owner = $selected['owner'];
$repo  = $selected['repo'];
$refIn = isset($_GET['ref']) ? $_GET['ref'] : (isset($_POST['ref']) ? $_POST['ref'] : $selected['ref']);
$ref   = trim($refIn);

// ---------- Simple lock (prevent concurrent runs) ----------
$lockFile = SITE_ROOT.'/.deploy.lock';
$lock = @fopen($lockFile, 'c+');
if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
  http_response_code(423);
  exit('Deployment already running. Try again later.');
}
ftruncate($lock, 0);
fwrite($lock, (string)time());

// ---------- Helpers ----------
function norm_path($p){ return str_replace('\\','/', $p); }
function starts_with($h,$n){ return strncmp($h,$n,strlen($n))===0; }
function ensure_dir($path){
  if (!is_dir($path) && !@mkdir($path, 0755, true)) {
    throw new RuntimeException("Failed to create dir: $path");
  }
}
function is_excluded($rel, $excludes){
  $rel = ltrim(norm_path($rel),'/');
  foreach ($excludes as $ex) {
    $ex = ltrim(norm_path($ex),'/');
    if ($rel === $ex || starts_with($rel, $ex.'/')) return true;
  }
  return false;
}
function rrmdir($dir){
  if (!is_dir($dir)) return;
  $it = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
  $ri = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
  foreach ($ri as $f) { $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname()); }
  @rmdir($dir);
}
function http_get_to_file($url, $outfile, $token=''){
  if (function_exists('curl_init')) {
    $ch = curl_init($url);
    $fp = fopen($outfile, 'w');
    curl_setopt_array($ch, [
      CURLOPT_FILE => $fp,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_TIMEOUT => TIMEOUT_SEC,
      CURLOPT_USERAGENT => 'zip-deployer/1.0',
      CURLOPT_HTTPHEADER => $token ? ["Authorization: Bearer $token"] : [],
    ]);
    $ok = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    fclose($fp);
    if (!$ok || $code >= 400) { @unlink($outfile); throw new RuntimeException("Download failed ($code): $err"); }
  } else {
    $ctx = stream_context_create(['http'=>[
      'method'=>'GET','timeout'=>TIMEOUT_SEC,
      'header'=>implode("\r\n", array_filter(['User-Agent: zip-deployer/1.0', $token ? "Authorization: Bearer $token" : null])),
    ]]);
    $data = @file_get_contents($url, false, $ctx);
    if ($data === false) throw new RuntimeException("Download failed (stream).");
    if (file_put_contents($outfile, $data) === false) throw new RuntimeException("Write failed: $outfile");
  }
}

// ---------- Begin ----------
header('Content-Type: text/plain; charset=utf-8');
echo "Deploying $owner/$repo @ $ref\n";

$tmpRoot   = SITE_ROOT . '/.deploy_tmp_' . bin2hex(random_bytes(4));
$zipPath   = $tmpRoot . '/repo.zip';
$stageDir  = $tmpRoot . '/stage';
$backupTs  = date('Ymd_His');
$backupRoot= SITE_ROOT . '/' . BACKUP_DIR . '/bk_' . $backupTs;

ensure_dir($tmpRoot);
ensure_dir($stageDir);
if (KEEP_BACKUP) ensure_dir(SITE_ROOT . '/' . BACKUP_DIR);

// 1) Build codeload ZIP URL (branch/tag/SHA)
$zipUrl = "https://codeload.github.com/".rawurlencode($owner)."/".rawurlencode($repo)."/zip/refs/heads/".rawurlencode($ref);
if (preg_match('/^v?\d+\.\d+\.\d+$/', $ref)) {
  // Looks like a tag "v1.2.3" → tags
  $zipUrl = "https://codeload.github.com/".rawurlencode($owner)."/".rawurlencode($repo)."/zip/refs/tags/".rawurlencode($ref);
}
echo "Downloading ZIP...\n";
http_get_to_file($zipUrl, $zipPath, GITHUB_TOKEN);

// 2) Unzip to stage
$zip = new ZipArchive();
if ($zip->open($zipPath) !== true) throw new RuntimeException("Cannot open ZIP.");
$zip->extractTo($stageDir);
$zip->close();
@unlink($zipPath);

// 3) Find extracted root (GitHub wraps contents inside repo-ref folder)
$entries = array_values(array_filter(scandir($stageDir), fn($x)=>$x!=='.' && $x!=='..'));
if (count($entries) !== 1 || !is_dir($stageDir.'/'.$entries[0])) throw new RuntimeException("Unexpected ZIP structure.");
$srcDir = $stageDir . '/' . $entries[0];

// 4) Index source for prune
$srcSet = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcDir, FilesystemIterator::SKIP_DOTS));
foreach ($it as $item) {
  $rel = substr(norm_path($item->getPathname()), strlen(norm_path($srcDir)) + 1);
  if (is_excluded($rel, EXCLUDES)) continue;
  $srcSet[$rel] = $item->isDir() ? 'dir' : 'file';
}

// 5) Backup overwritten + copy over
if (KEEP_BACKUP) echo "Backing up overwritten to ".norm_path($backupRoot)."/overwritten\n";
$it2 = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcDir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
foreach ($it2 as $item) {
  $rel = substr(norm_path($item->getPathname()), strlen(norm_path($srcDir)) + 1);
  if (is_excluded($rel, EXCLUDES)) continue;

  $dest = SITE_ROOT . '/' . $rel;
  if ($item->isDir()) {
    ensure_dir($dest);
  } else {
    if (KEEP_BACKUP && file_exists($dest)) {
      $bk = $backupRoot . '/overwritten/' . $rel;
      ensure_dir(dirname($bk));
      @copy($dest, $bk);
    }
    ensure_dir(dirname($dest));
    if (!@copy($item->getPathname(), $dest)) throw new RuntimeException("Copy failed: $rel");
    @chmod($dest, 0644);
  }
}

// 6) Prune removed
if (PRUNE_REMOVED) {
  echo "Pruning removed files...\n";
  $allIter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(SITE_ROOT, FilesystemIterator::SKIP_DOTS));
  $all = iterator_to_array($allIter, false);
  // Delete deeper paths first
  usort($all, fn($a,$b)=>substr_count($b->getPathname(), DIRECTORY_SEPARATOR) <=> substr_count($a->getPathname(), DIRECTORY_SEPARATOR));
  foreach ($all as $node) {
    $abs = norm_path($node->getPathname());
    $rel = ltrim(substr($abs, strlen(norm_path(SITE_ROOT))+1), '/');
    if ($rel === '' || is_excluded($rel, EXCLUDES)) continue;
    if (!isset($srcSet[$rel])) {
      if (KEEP_BACKUP && !$node->isDir()) {
        $bk = $backupRoot . '/removed/' . $rel;
        ensure_dir(dirname($bk));
        @copy($abs, $bk);
      }
      $node->isDir() ? @rmdir($abs) : @unlink($abs);
    }
  }
}

// 7) Cleanup
echo "Cleaning up temp...\n";
rrmdir($stageDir);
rrmdir($tmpRoot);
@unlink($lockFile);
fclose($lock);
echo "✅ Deploy complete.\n";
