<?php
const WPS_ASSET_BASE = '.';
const WPS_SETTINGS_URL = 'settings.php';

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/github.php';

$settings = wps_load_settings();
$repoRootPath = 'WebPublisherSystem';
$localRoot = realpath(__DIR__ . '/..');
$results = [];
$error = '';
$success = '';

function wps_sync_should_skip(string $repoPath): bool
{
    $normalized = trim(str_replace('\\', '/', $repoPath), '/');

    $skipPrefixes = [
        'WebPublisherSystem/platform/data',
    ];

    foreach ($skipPrefixes as $prefix) {
        if ($normalized === $prefix || str_starts_with($normalized, $prefix . '/')) {
            return true;
        }
    }

    return false;
}

function wps_sync_relative_path(string $repoPath): string
{
    $prefix = 'WebPublisherSystem/';
    if (str_starts_with($repoPath, $prefix)) {
        return substr($repoPath, strlen($prefix));
    }

    return $repoPath;
}

function wps_sync_download_raw(string $url): array
{
    $headers = [
        'User-Agent: WebPublisherSystem',
        'Accept: application/vnd.github.raw',
    ];

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $body = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($body === false || $error) {
            return ['ok' => false, 'body' => '', 'error' => $error ?: 'Raw download failed.', 'status' => $httpCode];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            return ['ok' => false, 'body' => '', 'error' => 'Raw download returned HTTP ' . $httpCode, 'status' => $httpCode];
        }

        return ['ok' => true, 'body' => $body, 'error' => '', 'status' => $httpCode];
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => implode("\r\n", $headers),
            'timeout' => 30,
        ],
    ]);

    $body = @file_get_contents($url, false, $context);
    if ($body === false) {
        return ['ok' => false, 'body' => '', 'error' => 'Raw download failed. cURL is unavailable and file_get_contents could not fetch the URL.', 'status' => 0];
    }

    return ['ok' => true, 'body' => $body, 'error' => '', 'status' => 200];
}


function wps_sync_via_git(array $settings, string $localRoot, array &$results): bool
{
    if (!function_exists('shell_exec')) {
        $results[] = ['status' => 'error', 'path' => 'git', 'message' => 'shell_exec is disabled, cannot run git-based sync.'];
        return false;
    }

    $owner = trim((string) ($settings['github_owner'] ?? ''));
    $repo = trim((string) ($settings['github_repo'] ?? ''));
    $branch = trim((string) ($settings['github_branch'] ?? 'main'));
    if ($owner === '' || $repo === '') {
        $results[] = ['status' => 'error', 'path' => 'git', 'message' => 'Missing GitHub owner/repo settings.'];
        return false;
    }

    $tmpDir = sys_get_temp_dir() . '/wps-sync-' . bin2hex(random_bytes(6));
    $repoUrl = 'https://github.com/' . rawurlencode($owner) . '/' . rawurlencode($repo) . '.git';
    @mkdir($tmpDir, 0700, true);

    $cloneCmd = 'git clone --depth 1 --branch ' . escapeshellarg($branch) . ' ' . escapeshellarg($repoUrl) . ' ' . escapeshellarg($tmpDir) . ' 2>&1';
    $cloneOut = shell_exec($cloneCmd);
    if (!is_dir($tmpDir . '/.git')) {
        $results[] = ['status' => 'error', 'path' => 'git clone', 'message' => trim((string) $cloneOut) ?: 'git clone failed.'];
        return false;
    }

    $sourcePath = $tmpDir . '/WebPublisherSystem';
    if (!is_dir($sourcePath)) {
        $results[] = ['status' => 'error', 'path' => 'WebPublisherSystem', 'message' => 'WebPublisherSystem folder not found in repository root.'];
        shell_exec('rm -rf ' . escapeshellarg($tmpDir));
        return false;
    }

    $rsyncCmd = 'rsync -a --delete --exclude platform/data/ --exclude .git/ ' . escapeshellarg($sourcePath . '/') . ' ' . escapeshellarg($localRoot . '/');
    $rsyncOutput = [];
    $rsyncCode = 0;
    exec($rsyncCmd . ' 2>&1', $rsyncOutput, $rsyncCode);
    $rsyncOut = trim(implode("\n", $rsyncOutput));

    if ($rsyncCode !== 0) {
        $results[] = ['status' => 'error', 'path' => 'rsync', 'message' => $rsyncOut ?: 'rsync failed.'];
        shell_exec('rm -rf ' . escapeshellarg($tmpDir));
        return false;
    }

    $results[] = ['status' => 'updated', 'path' => 'WebPublisherSystem/*', 'message' => 'Synced via git clone + rsync (platform/data preserved).'];
    if ($rsyncOut !== '') {
        $results[] = ['status' => 'info', 'path' => 'rsync', 'message' => $rsyncOut];
    }

    shell_exec('rm -rf ' . escapeshellarg($tmpDir));
    return true;
}

function wps_sync_path(array $settings, string $repoPath, string $localRoot, array &$results): void
{
    if (wps_sync_should_skip($repoPath)) {
        $results[] = ['status' => 'skipped', 'path' => $repoPath, 'message' => 'Runtime data path skipped.'];
        return;
    }

    $url = wps_github_api_url($settings, $repoPath);
    $response = wps_github_fetch_json($url);

    if (!$response['ok']) {
        $results[] = ['status' => 'error', 'path' => $repoPath, 'message' => $response['error']];
        return;
    }

    $items = $response['data'];

    if (isset($items['type'])) {
        $items = [$items];
    }

    if (!is_array($items)) {
        $results[] = ['status' => 'error', 'path' => $repoPath, 'message' => 'Unexpected GitHub response.'];
        return;
    }

    foreach ($items as $item) {
        if (!is_array($item) || empty($item['path']) || empty($item['type'])) {
            continue;
        }

        $path = $item['path'];

        if (wps_sync_should_skip($path)) {
            $results[] = ['status' => 'skipped', 'path' => $path, 'message' => 'Runtime data path skipped.'];
            continue;
        }

        if ($item['type'] === 'dir') {
            wps_sync_path($settings, $path, $localRoot, $results);
            continue;
        }

        if ($item['type'] !== 'file') {
            $results[] = ['status' => 'skipped', 'path' => $path, 'message' => 'Unsupported item type: ' . $item['type']];
            continue;
        }

        $relativePath = wps_sync_relative_path($path);
        $targetPath = $localRoot . '/' . $relativePath;
        $targetDir = dirname($targetPath);

        $realLocalRoot = realpath($localRoot);
        $realTargetDir = realpath($targetDir);

        if ($realTargetDir === false) {
            if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                $results[] = ['status' => 'error', 'path' => $path, 'message' => 'Could not create local directory.'];
                continue;
            }
            $realTargetDir = realpath($targetDir);
        }

        if ($realLocalRoot === false || $realTargetDir === false) {
            $results[] = ['status' => 'error', 'path' => $path, 'message' => 'Unsafe local path blocked.'];
            continue;
        }

        $rootPrefix = rtrim(str_replace('\\', '/', $realLocalRoot), '/') . '/';
        $targetPrefix = rtrim(str_replace('\\', '/', $realTargetDir), '/') . '/';

        if (!str_starts_with($targetPrefix, $rootPrefix)) {
            $results[] = ['status' => 'error', 'path' => $path, 'message' => 'Unsafe local path blocked.'];
            continue;
        }

        $downloadUrl = $item['download_url'] ?? '';
        if (!$downloadUrl) {
            $results[] = ['status' => 'error', 'path' => $path, 'message' => 'Missing GitHub download URL.'];
            continue;
        }

        $download = wps_sync_download_raw($downloadUrl);
        if (!$download['ok']) {
            $results[] = ['status' => 'error', 'path' => $path, 'message' => $download['error']];
            continue;
        }

        $existing = file_exists($targetPath) ? file_get_contents($targetPath) : null;
        if ($existing === $download['body']) {
            $results[] = ['status' => 'unchanged', 'path' => $relativePath, 'message' => 'Already up to date.'];
            continue;
        }

        if (file_put_contents($targetPath, $download['body']) === false) {
            $results[] = ['status' => 'error', 'path' => $relativePath, 'message' => 'Could not write local file. Check permissions.'];
            continue;
        }

        $results[] = ['status' => $existing === null ? 'created' : 'updated', 'path' => $relativePath, 'message' => 'Synced from GitHub.'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$localRoot) {
        $error = 'Could not resolve local WebPublisherSystem root folder.';
    } else {
        $gitSynced = wps_sync_via_git($settings, $localRoot, $results);
        if (!$gitSynced) {
            wps_sync_path($settings, $repoRootPath, $localRoot, $results);
        }
        $errors = array_filter($results, fn($item) => $item['status'] === 'error');
        $success = empty($errors)
            ? 'System sync completed successfully.'
            : 'System sync completed with ' . count($errors) . ' error(s).';
    }
}

wps_render_header('System Sync');
?>

<section class="panel">
    <h1>Sync WebPublisherSystem from GitHub</h1>
    <p class="muted">This first tries a <strong>git clone + rsync</strong> sync (to avoid GitHub API rate limits), then automatically falls back to GitHub API sync if git sync is unavailable. Runtime settings in <code>platform/data/</code> are always skipped so your saved settings are not overwritten.</p>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo wps_h($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert <?php echo str_contains($success, 'error') ? 'alert-error' : 'alert-success'; ?>"><?php echo wps_h($success); ?></div>
    <?php endif; ?>

    <form method="post" class="actions">
        <button type="submit">Sync All System Files from GitHub</button>
        <a class="button-secondary" href="settings.php">Back to Settings</a>
        <a class="button-secondary" href="<?php echo wps_h(wps_archive_url()); ?>">View Blog Archive</a>
    </form>
</section>

<?php if ($results): ?>
    <section class="panel">
        <h2>Sync Results</h2>
        <div class="result-box">
            <ul>
                <?php foreach ($results as $item): ?>
                    <li>
                        <strong><?php echo wps_h(strtoupper($item['status'])); ?></strong>
                        — <?php echo wps_h($item['path']); ?>
                        <br><small><?php echo wps_h($item['message']); ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
<?php endif; ?>

<?php wps_render_footer(); ?>
