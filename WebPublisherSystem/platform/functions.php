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
        <link rel="stylesheet" href="assets/style.css">
    </head>
    <body>
    <header class="site-header">
        <div class="container header-inner">
            <a class="brand" href="index.php"><?php echo wps_h($settings['site_name']); ?></a>
            <nav>
                <a href="index.php">Archive</a>
                <a href="settings.php">Settings</a>
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
