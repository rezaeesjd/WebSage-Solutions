<?php
/**
 * GitHub Import Engine
 * Standalone addon for syncing files from any GitHub repository or folder.
 * Compatible with PHP 7.4+. No external dependencies.
 *
 * Drop into any PHP project and adjust the constants below.
 */

defined('GHIMP_DATA_DIR')         || define('GHIMP_DATA_DIR',         __DIR__ . '/data');
defined('GHIMP_LOCAL_ROOT')       || define('GHIMP_LOCAL_ROOT',       (string) (realpath(__DIR__ . '/..') ?: dirname(__DIR__)));
defined('GHIMP_CONNECTIONS_FILE') || define('GHIMP_CONNECTIONS_FILE', GHIMP_DATA_DIR . '/github-import-connections.json');

/**
 * Paths relative to GHIMP_LOCAL_ROOT that must never be overwritten.
 * Add entries as needed for your project.
 */
defined('GHIMP_PROTECTED_PATHS') || define('GHIMP_PROTECTED_PATHS', [
    'platform/data',
]);

// ─── URL Parsing ──────────────────────────────────────────────────────────────

/**
 * Parse a GitHub URL or shorthand into owner/repo/branch/path components.
 * Supports:
 *   https://github.com/owner/repo
 *   https://github.com/owner/repo/tree/branch
 *   https://github.com/owner/repo/tree/branch/path/to/dir
 *   owner/repo
 *   owner/repo@branch
 */
function ghimp_parse_github_url(string $input): array
{
    $input = trim($input);
    $empty = ['owner' => '', 'repo' => '', 'branch' => 'main', 'path' => '', 'error' => ''];

    if (preg_match('#^https?://github\.com/(.+)$#i', $input, $m)) {
        $parts = array_values(array_filter(explode('/', $m[1]), fn($p) => $p !== ''));
        if (count($parts) < 2) {
            return array_merge($empty, ['error' => 'Could not extract owner and repo from URL.']);
        }

        $owner = $parts[0];
        $repo  = rtrim($parts[1], '.git');

        if (isset($parts[2]) && $parts[2] === 'tree' && isset($parts[3])) {
            $branch = $parts[3];
            $path   = implode('/', array_slice($parts, 4));
        } else {
            $branch = 'main';
            $path   = '';
        }

        return ['owner' => $owner, 'repo' => $repo, 'branch' => $branch, 'path' => $path, 'error' => ''];
    }

    // owner/repo@branch or owner/repo
    if (preg_match('#^([A-Za-z0-9_.-]+)/([A-Za-z0-9_.-]+)(?:@([A-Za-z0-9_./()\-]+))?$#', $input, $m)) {
        return [
            'owner'  => $m[1],
            'repo'   => rtrim($m[2], '.git'),
            'branch' => isset($m[3]) && $m[3] !== '' ? $m[3] : 'main',
            'path'   => '',
            'error'  => '',
        ];
    }

    return array_merge($empty, ['error' => 'Unrecognized format. Use https://github.com/owner/repo or owner/repo.']);
}

// ─── Connection Store ─────────────────────────────────────────────────────────

function ghimp_ensure_data_dir(): void
{
    if (!is_dir(GHIMP_DATA_DIR)) {
        mkdir(GHIMP_DATA_DIR, 0755, true);
    }
}

function ghimp_connections_load(): array
{
    ghimp_ensure_data_dir();
    if (!file_exists(GHIMP_CONNECTIONS_FILE)) {
        return [];
    }
    $json = file_get_contents(GHIMP_CONNECTIONS_FILE);
    $data = json_decode((string) $json, true);
    return is_array($data) ? $data : [];
}

function ghimp_connections_save(array $connections): bool
{
    ghimp_ensure_data_dir();
    $tmp     = GHIMP_CONNECTIONS_FILE . '.tmp';
    $written = file_put_contents($tmp, json_encode(array_values($connections), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    if ($written === false) {
        return false;
    }
    return rename($tmp, GHIMP_CONNECTIONS_FILE);
}

function ghimp_connection_add(string $url, string $branch, string $path, string $token, string $localPath): array
{
    $parsed = ghimp_parse_github_url($url);
    if ($parsed['error'] !== '') {
        return ['ok' => false, 'error' => $parsed['error'], 'connection' => null];
    }

    if ($branch !== '') {
        $parsed['branch'] = $branch;
    }
    if ($path !== '') {
        $parsed['path'] = trim($path, '/');
    }

    $id = bin2hex(random_bytes(8));

    $connection = [
        'id'          => $id,
        'url'         => $url,
        'owner'       => $parsed['owner'],
        'repo'        => $parsed['repo'],
        'branch'      => $parsed['branch'],
        'path'        => $parsed['path'],
        'token'       => $token,
        'local_path'  => trim($localPath, '/'),
        'enabled'     => true,
        'last_sync'   => null,
        'last_status' => 'neutral',
    ];

    $connections   = ghimp_connections_load();
    $connections[] = $connection;
    ghimp_connections_save($connections);

    return ['ok' => true, 'error' => '', 'connection' => $connection];
}

function ghimp_connection_remove(string $id): void
{
    $connections = ghimp_connections_load();
    $connections = array_values(array_filter($connections, fn($c) => $c['id'] !== $id));
    ghimp_connections_save($connections);
}

function ghimp_connection_toggle(string $id): void
{
    $connections = ghimp_connections_load();
    foreach ($connections as &$conn) {
        if ($conn['id'] === $id) {
            $conn['enabled'] = !($conn['enabled'] ?? true);
            break;
        }
    }
    unset($conn);
    ghimp_connections_save($connections);
}

function ghimp_connection_update_status(string $id, string $status): void
{
    $connections = ghimp_connections_load();
    foreach ($connections as &$conn) {
        if ($conn['id'] === $id) {
            $conn['last_sync']   = gmdate('c');
            $conn['last_status'] = $status;
            break;
        }
    }
    unset($conn);
    ghimp_connections_save($connections);
}

// ─── Path Safety ──────────────────────────────────────────────────────────────

function ghimp_is_protected(string $relativePath): bool
{
    $normalized = trim(str_replace('\\', '/', $relativePath), '/');
    foreach (GHIMP_PROTECTED_PATHS as $protected) {
        $p = trim((string) $protected, '/');
        if ($normalized === $p || str_starts_with($normalized, $p . '/')) {
            return true;
        }
    }
    return false;
}

// ─── HTTP Helpers ─────────────────────────────────────────────────────────────

function ghimp_http_get(string $url, array $extraHeaders = []): array
{
    $headers = array_merge(['User-Agent: GitHubImportAddon/1.0'], $extraHeaders);

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $body = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($body === false || $err !== '') {
            return ['ok' => false, 'body' => '', 'status' => $code, 'error' => $err ?: 'Request failed.'];
        }
        if ($code < 200 || $code >= 300) {
            return ['ok' => false, 'body' => (string) $body, 'status' => $code, 'error' => 'HTTP ' . $code];
        }
        return ['ok' => true, 'body' => (string) $body, 'status' => $code, 'error' => ''];
    }

    $ctx  = stream_context_create([
        'http' => ['method' => 'GET', 'header' => implode("\r\n", $headers), 'timeout' => 60],
    ]);
    $body = @file_get_contents($url, false, $ctx);
    $code = 0;
    if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $matches)) {
        $code = (int) $matches[1];
    }
    if ($body === false) {
        return ['ok' => false, 'body' => '', 'status' => 0, 'error' => 'Request failed. cURL unavailable.'];
    }
    if ($code >= 300) {
        return ['ok' => false, 'body' => $body, 'status' => $code, 'error' => 'HTTP ' . $code];
    }
    return ['ok' => true, 'body' => $body, 'status' => $code, 'error' => ''];
}

function ghimp_auth_headers(string $token): array
{
    if ($token === '') {
        return [];
    }
    return ['Authorization: Bearer ' . $token, 'Accept: application/vnd.github+json'];
}

// ─── Write Helper ─────────────────────────────────────────────────────────────

function ghimp_write_file(string $localRoot, string $relativePath, string $content, array &$results): void
{
    $relativePath = trim(str_replace('\\', '/', $relativePath), '/');

    if ($relativePath === '') {
        $results[] = ['status' => 'skipped', 'path' => '(empty)', 'message' => 'Empty path skipped.'];
        return;
    }

    if (ghimp_is_protected($relativePath)) {
        $results[] = ['status' => 'skipped', 'path' => $relativePath, 'message' => 'Protected path skipped.'];
        return;
    }

    $realRoot = realpath($localRoot);
    if ($realRoot === false) {
        $results[] = ['status' => 'error', 'path' => $relativePath, 'message' => 'Cannot resolve local root: ' . $localRoot];
        return;
    }

    $target    = $realRoot . '/' . $relativePath;
    $targetDir = dirname($target);

    if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
        $results[] = ['status' => 'error', 'path' => $relativePath, 'message' => 'Cannot create directory.'];
        return;
    }

    $realDir = realpath($targetDir);
    if ($realDir === false) {
        $results[] = ['status' => 'error', 'path' => $relativePath, 'message' => 'Cannot resolve target directory.'];
        return;
    }

    $rootPrefix   = rtrim(str_replace('\\', '/', $realRoot), '/') . '/';
    $targetPrefix = rtrim(str_replace('\\', '/', $realDir), '/')  . '/';
    if (!str_starts_with($targetPrefix, $rootPrefix)) {
        $results[] = ['status' => 'error', 'path' => $relativePath, 'message' => 'Path traversal blocked.'];
        return;
    }

    $existing = file_exists($target) ? file_get_contents($target) : null;
    if ($existing === $content) {
        $results[] = ['status' => 'unchanged', 'path' => $relativePath, 'message' => 'Already up to date.'];
        return;
    }

    if (file_put_contents($target, $content) === false) {
        $results[] = ['status' => 'error', 'path' => $relativePath, 'message' => 'Write failed. Check permissions.'];
        return;
    }

    $results[] = ['status' => $existing === null ? 'created' : 'updated', 'path' => $relativePath, 'message' => 'Synced from GitHub.'];
}

// ─── Sync via ZIP ─────────────────────────────────────────────────────────────

function ghimp_sync_via_zip(array $conn, string $targetRoot, array &$results): bool
{
    if (!class_exists('ZipArchive')) {
        $results[] = ['status' => 'error', 'path' => 'zip', 'message' => 'ZipArchive unavailable; falling back to API.'];
        return false;
    }

    $owner   = $conn['owner'];
    $repo    = $conn['repo'];
    $branch  = $conn['branch'];
    $subPath = trim($conn['path'] ?? '', '/');

    $zipUrl  = 'https://codeload.github.com/' . rawurlencode($owner) . '/' . rawurlencode($repo) . '/zip/refs/heads/' . rawurlencode($branch);
    $headers = array_merge(['Accept: application/octet-stream'], ghimp_auth_headers($conn['token'] ?? ''));
    $dl      = ghimp_http_get($zipUrl, $headers);

    if (!$dl['ok']) {
        $results[] = ['status' => 'error', 'path' => 'zip', 'message' => 'ZIP download failed: ' . $dl['error']];
        return false;
    }

    $zipPath = tempnam(sys_get_temp_dir(), 'ghimp-');
    if ($zipPath === false || file_put_contents($zipPath, $dl['body']) === false) {
        $results[] = ['status' => 'error', 'path' => 'zip', 'message' => 'Cannot write temp ZIP file.'];
        return false;
    }

    $zip = new ZipArchive();
    if ($zip->open($zipPath) !== true) {
        @unlink($zipPath);
        $results[] = ['status' => 'error', 'path' => 'zip', 'message' => 'Cannot open downloaded ZIP.'];
        return false;
    }

    // The first entry in a GitHub ZIP is always "owner-repo-ref/" — detect it.
    $stripPrefix = '';
    $synced      = 0;

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = str_replace('\\', '/', (string) $zip->getNameIndex($i));

        if (str_ends_with($name, '/')) {
            if ($stripPrefix === '' && substr_count(rtrim($name, '/'), '/') === 0) {
                $stripPrefix = $name;
            }
            continue;
        }

        if ($stripPrefix === '' && str_contains($name, '/')) {
            $stripPrefix = substr($name, 0, strpos($name, '/') + 1);
        }

        $relative = $stripPrefix !== '' && str_starts_with($name, $stripPrefix)
            ? substr($name, strlen($stripPrefix))
            : $name;

        // If a repo subfolder is configured, only include files under it.
        if ($subPath !== '') {
            $prefix = $subPath . '/';
            if (!str_starts_with($relative, $prefix)) {
                continue;
            }
            $relative = substr($relative, strlen($prefix));
        }

        if ($relative === '') {
            continue;
        }

        $content = $zip->getFromIndex($i);
        if ($content === false) {
            $results[] = ['status' => 'error', 'path' => $relative, 'message' => 'Cannot read entry from ZIP.'];
            continue;
        }

        ghimp_write_file($targetRoot, $relative, $content, $results);
        $synced++;
    }

    $zip->close();
    @unlink($zipPath);

    if ($synced === 0) {
        $results[] = ['status' => 'error', 'path' => 'zip', 'message' => 'No matching files found in ZIP' . ($subPath !== '' ? " under /{$subPath}" : '') . '.'];
        return false;
    }

    return true;
}

// ─── Sync via GitHub API ──────────────────────────────────────────────────────

function ghimp_api_contents_url(array $conn, string $repoPath = ''): string
{
    $owner   = rawurlencode($conn['owner']);
    $repo    = rawurlencode($conn['repo']);
    $branch  = rawurlencode($conn['branch']);
    $clean   = trim($repoPath, '/');
    $encoded = $clean !== '' ? '/' . implode('/', array_map('rawurlencode', explode('/', $clean))) : '';
    return "https://api.github.com/repos/{$owner}/{$repo}/contents{$encoded}?ref={$branch}";
}

function ghimp_sync_via_api(array $conn, string $repoPath, string $targetRoot, string $basePath, array &$results): void
{
    $headers = array_merge(
        ['User-Agent: GitHubImportAddon/1.0', 'Accept: application/vnd.github+json'],
        !empty($conn['token']) ? ['Authorization: Bearer ' . $conn['token']] : []
    );

    $url  = ghimp_api_contents_url($conn, $repoPath);
    $resp = ghimp_http_get($url, $headers);

    if (!$resp['ok']) {
        $results[] = ['status' => 'error', 'path' => $repoPath ?: '(root)', 'message' => 'API error: ' . $resp['error']];
        return;
    }

    $items = json_decode($resp['body'], true);
    if (!is_array($items)) {
        $results[] = ['status' => 'error', 'path' => $repoPath ?: '(root)', 'message' => 'Unexpected API response.'];
        return;
    }

    // Single-file response has a 'type' key at the top level.
    if (isset($items['type'])) {
        $items = [$items];
    }

    foreach ($items as $item) {
        if (!is_array($item) || empty($item['type'])) {
            continue;
        }

        $itemPath = $item['path'] ?? '';

        if ($item['type'] === 'dir') {
            ghimp_sync_via_api($conn, $itemPath, $targetRoot, $basePath, $results);
            continue;
        }

        if ($item['type'] !== 'file') {
            continue;
        }

        // Strip the basePath prefix so the local path mirrors only the sub-tree.
        $relativePath = $itemPath;
        if ($basePath !== '' && str_starts_with($relativePath, $basePath . '/')) {
            $relativePath = substr($relativePath, strlen($basePath) + 1);
        }

        $downloadUrl = $item['download_url'] ?? '';
        if ($downloadUrl === '') {
            $results[] = ['status' => 'error', 'path' => $itemPath, 'message' => 'No download URL from API.'];
            continue;
        }

        $dlHeaders = !empty($conn['token']) ? ['Authorization: Bearer ' . $conn['token']] : [];
        $dl        = ghimp_http_get($downloadUrl, $dlHeaders);
        if (!$dl['ok']) {
            $results[] = ['status' => 'error', 'path' => $itemPath, 'message' => 'Download failed: ' . $dl['error']];
            continue;
        }

        ghimp_write_file($targetRoot, $relativePath, $dl['body'], $results);
    }
}

// ─── Test Connection ──────────────────────────────────────────────────────────

function ghimp_test_connection(array $conn): array
{
    $headers = array_merge(
        ['User-Agent: GitHubImportAddon/1.0', 'Accept: application/vnd.github+json'],
        !empty($conn['token']) ? ['Authorization: Bearer ' . $conn['token']] : []
    );

    $url  = ghimp_api_contents_url($conn, $conn['path'] ?? '');
    $resp = ghimp_http_get($url, $headers);

    if (!$resp['ok']) {
        return ['ok' => false, 'message' => 'Connection failed: ' . $resp['error']];
    }

    $data  = json_decode($resp['body'], true);
    $count = is_array($data) ? (isset($data['type']) ? 1 : count($data)) : 0;
    return ['ok' => true, 'message' => 'Connected — ' . $count . ' item(s) found at the configured path.'];
}

// ─── Sync One Connection ──────────────────────────────────────────────────────

function ghimp_sync_connection(array $conn): array
{
    $localPath  = trim($conn['local_path'] ?? '', '/');
    $targetRoot = $localPath !== ''
        ? GHIMP_LOCAL_ROOT . '/' . $localPath
        : GHIMP_LOCAL_ROOT;

    $results = [];
    $zipOk   = ghimp_sync_via_zip($conn, $targetRoot, $results);

    if (!$zipOk) {
        $basePath = trim($conn['path'] ?? '', '/');
        ghimp_sync_via_api($conn, $basePath, $targetRoot, $basePath, $results);
    }

    $errors = array_filter($results, fn($r) => $r['status'] === 'error');
    $total  = count($results);
    $status = count($errors) === 0 ? 'ok' : (count($errors) < $total ? 'partial' : 'error');

    ghimp_connection_update_status($conn['id'], $status);

    return ['results' => $results, 'status' => $status];
}

// ─── Sync All Enabled ─────────────────────────────────────────────────────────

function ghimp_sync_all_enabled(): array
{
    $all = [];
    foreach (ghimp_connections_load() as $conn) {
        if (!($conn['enabled'] ?? true)) {
            continue;
        }
        $all[$conn['id']] = ghimp_sync_connection($conn);
    }
    return $all;
}

// ─── Summary Stats ────────────────────────────────────────────────────────────

function ghimp_summary(): array
{
    $connections = ghimp_connections_load();
    $total       = count($connections);
    $enabled     = count(array_filter($connections, fn($c) => $c['enabled'] ?? true));
    $lastSync    = null;

    foreach ($connections as $conn) {
        if (!empty($conn['last_sync'])) {
            if ($lastSync === null || $conn['last_sync'] > $lastSync) {
                $lastSync = $conn['last_sync'];
            }
        }
    }

    return [
        'total'     => $total,
        'enabled'   => $enabled,
        'last_sync' => $lastSync,
    ];
}
