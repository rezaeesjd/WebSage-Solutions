<?php
require_once __DIR__ . '/functions.php';

$settings = wps_load_settings();
$error = '';
$success = '';

$archiveSlug = wps_archive_slug_from_setting($settings);
$archivePrefix = rtrim(wps_system_url_base(), '/') . '/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings['site_name'] = trim($_POST['site_name'] ?? $settings['site_name']);
    $settings['archive_title'] = trim($_POST['archive_title'] ?? $settings['archive_title']);
    $settings['archive_description'] = trim($_POST['archive_description'] ?? $settings['archive_description']);
    $rawArchiveSlug = trim((string) ($_POST['archive_slug'] ?? $archiveSlug));
    $cleanArchiveSlug = wps_sanitize_archive_slug($rawArchiveSlug);
    $settings['archive_base_url'] = $cleanArchiveSlug === '' ? 'blog' : $cleanArchiveSlug;

    if (wps_save_settings($settings)) {
        wps_ensure_archive_alias($settings);
        $success = 'Settings saved.';
    } else {
        $error = 'Could not save settings. Make sure the platform/data folder is writable.';
    }

    $settings = wps_load_settings();
    $archiveSlug = wps_archive_slug_from_setting($settings);
    $archivePrefix = rtrim(wps_system_url_base(), '/') . '/';
}

wps_render_header('Settings');
?>

<section class="panel">
    <h1>WebPublisherSystem Settings</h1>
    <p class="muted">Configure the public blog archive. Content updates and generated blog files are managed through System Sync and the per-post editor.</p>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo wps_h($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo wps_h($success); ?></div>
    <?php endif; ?>
</section>

<section class="panel">
    <h2>System update</h2>
    <p>This updates the uploaded WebPublisherSystem files from GitHub. Your local settings and single-post edits in <code>platform/data/</code> are skipped.</p>
    <div class="actions">
        <a class="button-secondary" href="system-sync.php">Open System Sync</a>
    </div>
</section>

<section class="panel">
    <h2>Configuration</h2>
    <form method="post" class="form grid-form">
        <label>
            Site name
            <input type="text" name="site_name" value="<?php echo wps_h($settings['site_name']); ?>" required>
        </label>

        <label>
            Archive title
            <input type="text" name="archive_title" value="<?php echo wps_h($settings['archive_title']); ?>" required>
        </label>

        <label class="full">
            Archive description
            <textarea name="archive_description" rows="3"><?php echo wps_h($settings['archive_description']); ?></textarea>
        </label>

        <div class="full field-block archive-slug-field">
            <label for="archive_slug">Archive slug</label>
            <div class="url-slug-row">
                <span class="url-slug-prefix" title="<?php echo wps_h($archivePrefix); ?>"><?php echo wps_h($archivePrefix); ?></span>
                <input id="archive_slug" type="text" name="archive_slug" value="<?php echo wps_h($archiveSlug); ?>" placeholder="blog" pattern="[a-zA-Z0-9_\-/]*">
                <span class="url-slug-suffix">/</span>
            </div>
            <small>Enter only the slug, for example <code>blog</code>, <code>blogs2</code>, or <code>travel-guides</code>. It will be created inside WebPublisherSystem.</small>
            <div class="archive-url-actions">
                <a class="button-secondary" href="<?php echo wps_h(wps_archive_url()); ?>" target="_blank" rel="noopener">Open Blog Archive in New Tab</a>
            </div>
        </div>

        <div class="full actions">
            <button type="submit">Save Settings</button>
        </div>
    </form>
</section>

<?php
require_once __DIR__ . '/github-import-engine.php';
$ghimpSummary = ghimp_summary();
?>

<section class="panel">
    <h2>GitHub Import</h2>
    <p class="muted">Import and sync files from any GitHub repository or folder. Each connection is managed independently and synced on demand.</p>

    <div class="ghimp-settings-row">
        <div class="ghimp-settings-stat">
            <strong><?php echo (int) $ghimpSummary['total']; ?></strong>
            <span>connection<?php echo $ghimpSummary['total'] === 1 ? '' : 's'; ?></span>
        </div>
        <div class="ghimp-settings-stat">
            <strong><?php echo (int) $ghimpSummary['enabled']; ?></strong>
            <span>enabled</span>
        </div>
        <div class="ghimp-settings-stat">
            <strong><?php echo $ghimpSummary['last_sync'] !== null ? wps_h(substr($ghimpSummary['last_sync'], 0, 10)) : '—'; ?></strong>
            <span>last sync</span>
        </div>
    </div>

    <div class="actions" style="margin-top: 16px;">
        <a class="button-secondary" href="github-import.php">Manage GitHub Import</a>
    </div>
</section>

<?php wps_render_footer(); ?>
