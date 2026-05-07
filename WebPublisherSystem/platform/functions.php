<?php
/**
 * Shared helpers for WebPublisherSystem.
 * Standalone, no WordPress, no password in this phase.
 */

const WPS_DATA_DIR = __DIR__ . '/data';
const WPS_SETTINGS_FILE = WPS_DATA_DIR . '/settings.json';

function wps_ensure_data_dir(): void
{
    if (!is_dir(WPS_DATA_DIR)) {
        mkdir(WPS_DATA_DIR, 0755, true);
    }
}

function wps_default_settings(): array
{
    return [
        'site_name' => 'Milano Adventures',
        'archive_title' => 'Travel Guides & Tour Ideas',
        'archive_description' => 'Helpful travel guides, tour ideas, and booking-focused articles from Milano Adventures.',
        'archive_base_url' => '',
        'github_owner' => 'rezaeesjd',
        'github_repo' => 'WebSage-Solutions',
        'github_branch' => 'main',
        'github_content_path' => 'WebPublisherSystem/content-system/tours',
        'website_link' => '{{WebsiteLink}}',
        'tripadvisor_link' => '{{TripAdvisorLink}}',
        'viator_link' => '{{ViatorLink}}',
        'updated_at' => gmdate('c'),
    ];
}

function wps_load_settings(): array
{
    wps_ensure_data_dir();

    if (!file_exists(WPS_SETTINGS_FILE)) {
        return wps_default_settings();
    }

    $json = file_get_contents(WPS_SETTINGS_FILE);
    $data = json_decode($json, true);

    if (!is_array($data)) {
        return wps_default_settings();
    }

    return array_merge(wps_default_settings(), $data);
}

function wps_save_settings(array $settings): bool
{
    wps_ensure_data_dir();
    $settings['updated_at'] = gmdate('c');

    return file_put_contents(
        WPS_SETTINGS_FILE,
        json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    ) !== false;
}

function wps_h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function wps_current_url_base(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');

    return $scheme . '://' . $host . ($scriptDir ? $scriptDir : '');
}

function wps_asset_url(string $path): string
{
    $base = defined('WPS_ASSET_BASE') ? trim((string) WPS_ASSET_BASE) : '';
    $cleanPath = ltrim($path, '/');

    if ($base === '') {
        return $cleanPath;
    }

    return rtrim($base, '/') . '/' . $cleanPath;
}

function wps_normalize_archive_base_url(string $value): string
{
    $trimmed = trim($value);
    if ($trimmed === '') {
        return '';
    }

    if (preg_match('#^https?://#i', $trimmed)) {
        return rtrim($trimmed, '/') . '/';
    }

    $path = '/' . trim($trimmed, '/');
    if ($path === '/') {
        return '/';
    }

    return $path . '/';
}

function wps_archive_url(): string
{
    if (defined('WPS_ARCHIVE_URL')) {
        return WPS_ARCHIVE_URL;
    }

    $settings = wps_load_settings();
    $archiveUrl = wps_normalize_archive_base_url((string) ($settings['archive_base_url'] ?? ''));

    if ($archiveUrl !== '') {
        return $archiveUrl;
    }

    return '/blog/';
}


function wps_archive_slug_from_setting(array $settings): string
{
    $archiveUrl = wps_normalize_archive_base_url((string) ($settings['archive_base_url'] ?? ''));
    if ($archiveUrl === '') {
        return '';
    }

    if (preg_match('#^https?://#i', $archiveUrl)) {
        $path = (string) parse_url($archiveUrl, PHP_URL_PATH);
        return trim($path, '/');
    }

    return trim($archiveUrl, '/');
}

function wps_sanitize_archive_slug(string $slug): string
{
    $parts = array_filter(explode('/', trim($slug, '/')), fn($part) => $part !== '');
    $safe = [];

    foreach ($parts as $part) {
        if ($part === '.' || $part === '..') {
            return '';
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $part)) {
            return '';
        }

        $safe[] = $part;
    }

    return implode('/', $safe);
}

function wps_ensure_archive_alias(array $settings): void
{
    $slug = wps_sanitize_archive_slug(wps_archive_slug_from_setting($settings));
    $root = realpath(__DIR__ . '/..');
    if ($root === false || $slug === '' || $slug === 'blog') {
        return;
    }

    $aliasDir = $root . '/' . $slug;
    $aliasRealParent = realpath(dirname($aliasDir));
    if ($aliasRealParent !== false) {
        $rootPrefix = rtrim(str_replace('\\', '/', $root), '/') . '/';
        $parentPrefix = rtrim(str_replace('\\', '/', $aliasRealParent), '/') . '/';
        if (!str_starts_with($parentPrefix, $rootPrefix)) {
            return;
        }
    }

    if (!is_dir($aliasDir)) {
        mkdir($aliasDir, 0755, true);
    }

    $marker = $aliasDir . '/.wps-archive-alias';
    $indexFile = $aliasDir . '/index.php';
    $postFile = $aliasDir . '/post.php';

    file_put_contents($marker, "managed-by=WebPublisherSystem
");
    file_put_contents($indexFile, "<?php
require_once __DIR__ . '/../blog/index.php';
");
    file_put_contents($postFile, "<?php
require_once __DIR__ . '/../blog/post.php';
");
}

function wps_redirect_legacy_blog_path_if_needed(array $settings): void
{
    $slug = wps_sanitize_archive_slug(wps_archive_slug_from_setting($settings));
    if ($slug === '' || $slug === 'blog') {
        return;
    }

    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $scriptDir = trim(str_replace('\\', '/', dirname($scriptName)), '/');
    if ($scriptDir === '' || $scriptDir === '.') {
        return;
    }

    $scriptSegments = explode('/', $scriptDir);
    $currentArchivePath = end($scriptSegments);
    if ($currentArchivePath === $slug) {
        return;
    }

    $targetBase = wps_archive_url();
    $isPost = basename($scriptName) === 'post.php';
    if ($isPost) {
        $slugParam = isset($_GET['slug']) ? (string) $_GET['slug'] : '';
        $target = rtrim($targetBase, '/') . '/post.php' . ($slugParam !== '' ? '?slug=' . rawurlencode($slugParam) : '');
        header('Location: ' . $target, true, 302);
        exit;
    }

    header('Location: ' . $targetBase, true, 302);
    exit;
}

function wps_settings_url(): string
{
    return defined('WPS_SETTINGS_URL') ? WPS_SETTINGS_URL : 'settings.php';
}

function wps_render_header(string $title): void
{
    $settings = wps_load_settings();
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo wps_h($title); ?> | <?php echo wps_h($settings['site_name']); ?></title>
        <link rel="stylesheet" href="<?php echo wps_h(wps_asset_url('assets/style.css')); ?>">
    </head>
    <body>
    <header class="site-header">
        <div class="container header-inner">
            <a class="brand" href="<?php echo wps_h(wps_archive_url()); ?>"><?php echo wps_h($settings['site_name']); ?></a>
            <nav>
                <a href="<?php echo wps_h(wps_archive_url()); ?>">Archive</a>
                <a href="<?php echo wps_h(wps_settings_url()); ?>">Settings</a>
            </nav>
        </div>
    </header>
    <main class="container">
    <?php
}

function wps_render_footer(): void
{
    ?>
    </main>
    <footer class="site-footer">
        <div class="container">
            <p>WebPublisherSystem. Public GitHub connection enabled. Publishing/sync will be expanded in the next phase.</p>
        </div>
    </footer>
    </body>
    </html>
    <?php
}
