<?php
/**
 * Shared helpers for the standalone SEO Blog Publisher.
 * This is intentionally simple and framework-free.
 */

session_start();

const SBP_DATA_DIR = __DIR__ . '/data';
const SBP_SETTINGS_FILE = SBP_DATA_DIR . '/settings.json';

function sbp_ensure_data_dir(): void
{
    if (!is_dir(SBP_DATA_DIR)) {
        mkdir(SBP_DATA_DIR, 0755, true);
    }
}

function sbp_default_settings(): array
{
    return [
        'installed' => false,
        'password_hash' => '',
        'site_name' => 'Milano Adventures',
        'archive_title' => 'Travel Guides & Tour Ideas',
        'archive_description' => 'Helpful travel guides, tour ideas, and booking-focused articles from Milano Adventures.',
        'archive_base_url' => '',
        'github_owner' => 'rezaeesjd',
        'github_repo' => 'WebSage-Solutions',
        'github_branch' => 'main',
        'github_content_path' => 'seo-content-system/tours',
        'website_link' => '{{WebsiteLink}}',
        'tripadvisor_link' => '{{TripAdvisorLink}}',
        'viator_link' => '{{ViatorLink}}',
        'default_status' => 'draft',
        'updated_at' => gmdate('c'),
    ];
}

function sbp_load_settings(): array
{
    sbp_ensure_data_dir();

    if (!file_exists(SBP_SETTINGS_FILE)) {
        return sbp_default_settings();
    }

    $json = file_get_contents(SBP_SETTINGS_FILE);
    $data = json_decode($json, true);

    if (!is_array($data)) {
        return sbp_default_settings();
    }

    return array_merge(sbp_default_settings(), $data);
}

function sbp_save_settings(array $settings): bool
{
    sbp_ensure_data_dir();
    $settings['updated_at'] = gmdate('c');

    return file_put_contents(
        SBP_SETTINGS_FILE,
        json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    ) !== false;
}

function sbp_is_installed(): bool
{
    $settings = sbp_load_settings();
    return !empty($settings['installed']) && !empty($settings['password_hash']);
}

function sbp_is_logged_in(): bool
{
    return !empty($_SESSION['sbp_logged_in']);
}

function sbp_require_login(): void
{
    if (!sbp_is_logged_in()) {
        header('Location: settings.php');
        exit;
    }
}

function sbp_h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function sbp_csrf_token(): string
{
    if (empty($_SESSION['sbp_csrf'])) {
        $_SESSION['sbp_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['sbp_csrf'];
}

function sbp_verify_csrf(): bool
{
    return isset($_POST['csrf']) && hash_equals($_SESSION['sbp_csrf'] ?? '', $_POST['csrf']);
}

function sbp_current_url_base(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');

    return $scheme . '://' . $host . ($scriptDir ? $scriptDir : '');
}

function sbp_render_header(string $title): void
{
    $settings = sbp_load_settings();
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo sbp_h($title); ?> | <?php echo sbp_h($settings['site_name']); ?></title>
        <link rel="stylesheet" href="assets/style.css">
    </head>
    <body>
    <header class="site-header">
        <div class="container header-inner">
            <a class="brand" href="index.php"><?php echo sbp_h($settings['site_name']); ?></a>
            <nav>
                <a href="index.php">Archive</a>
                <a href="settings.php">Settings</a>
                <?php if (sbp_is_logged_in()): ?>
                    <a href="logout.php">Logout</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main class="container">
    <?php
}

function sbp_render_footer(): void
{
    ?>
    </main>
    <footer class="site-footer">
        <div class="container">
            <p>SEO Blog Publisher shell. GitHub sync will be added in a later phase.</p>
        </div>
    </footer>
    </body>
    </html>
    <?php
}
