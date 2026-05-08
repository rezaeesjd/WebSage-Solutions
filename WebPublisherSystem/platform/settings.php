<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/github.php';

$settings = wps_load_settings();
$error = '';
$success = '';
$connection = null;

$archiveSlug = wps_archive_slug_from_setting($settings);
$archivePrefix = rtrim(wps_system_url_base(), '/') . '/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    $settings['site_name'] = trim($_POST['site_name'] ?? $settings['site_name']);
    $settings['archive_title'] = trim($_POST['archive_title'] ?? $settings['archive_title']);
    $settings['archive_description'] = trim($_POST['archive_description'] ?? $settings['archive_description']);
    $rawArchiveSlug = trim((string) ($_POST['archive_slug'] ?? $archiveSlug));
    $cleanArchiveSlug = wps_sanitize_archive_slug($rawArchiveSlug);
    $settings['archive_base_url'] = $cleanArchiveSlug === '' ? 'blog' : $cleanArchiveSlug;
    $settings['github_owner'] = trim($_POST['github_owner'] ?? $settings['github_owner']);
    $settings['github_repo'] = trim($_POST['github_repo'] ?? $settings['github_repo']);
    $settings['github_branch'] = trim($_POST['github_branch'] ?? $settings['github_branch']);
    $settings['github_content_path'] = trim($_POST['github_content_path'] ?? $settings['github_content_path']);
    $settings['website_link'] = trim($_POST['website_link'] ?? $settings['website_link']);
    $settings['tripadvisor_link'] = trim($_POST['tripadvisor_link'] ?? $settings['tripadvisor_link']);
    $settings['viator_link'] = trim($_POST['viator_link'] ?? $settings['viator_link']);

    if (wps_save_settings($settings)) {
        wps_ensure_archive_alias($settings);
        $success = 'Settings saved.';
    } else {
        $error = 'Could not save settings. Make sure the platform/data folder is writable.';
    }

    if ($action === 'test_connection') {
        $connection = wps_test_github_connection($settings);
    }

    $settings = wps_load_settings();
    $archiveSlug = wps_archive_slug_from_setting($settings);
    $archivePrefix = rtrim(wps_system_url_base(), '/') . '/';
}

wps_render_header('Settings');
?>

<section class="panel">
    <h1>WebPublisherSystem Settings</h1>
    <p class="muted">Configure the public archive and connect it to your public GitHub repository. No password is used in this phase.</p>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo wps_h($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo wps_h($success); ?></div>
    <?php endif; ?>

    <?php if ($connection): ?>
        <div class="alert <?php echo $connection['ok'] ? 'alert-success' : 'alert-error'; ?>">
            <strong><?php echo wps_h($connection['message']); ?></strong><br>
            <small><?php echo wps_h($connection['url']); ?></small>
        </div>

        <?php if (!empty($connection['items'])): ?>
            <div class="result-box">
                <h3>Items found</h3>
                <ul>
                    <?php foreach ($connection['items'] as $item): ?>
                        <li>
                            <strong><?php echo wps_h($item['name']); ?></strong>
                            <span class="muted">(<?php echo wps_h($item['type']); ?>)</span>
                            <br><small><?php echo wps_h($item['path']); ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>

<section class="panel">
    <h2>System update</h2>
    <p>This updates the uploaded WebPublisherSystem files from the public GitHub repository. Your saved settings in <code>platform/data/</code> are skipped.</p>
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
            <div class="archive-url-actions">
                <a class="button-secondary" href="<?php echo wps_h(wps_archive_url()); ?>" target="_blank" rel="noopener">Open Blog Archive in New Tab</a>
            </div>
            <small>Enter only the slug, for example <code>blog</code>, <code>blogs2</code>, or <code>travel-guides</code>. It will be created inside WebPublisherSystem.</small>
        </div>

        <h3 class="full">Public GitHub source</h3>

        <label>
            GitHub owner
            <input type="text" name="github_owner" value="<?php echo wps_h($settings['github_owner']); ?>">
        </label>

        <label>
            GitHub repo
            <input type="text" name="github_repo" value="<?php echo wps_h($settings['github_repo']); ?>">
        </label>

        <label>
            GitHub branch
            <input type="text" name="github_branch" value="<?php echo wps_h($settings['github_branch']); ?>">
        </label>

        <label>
            GitHub content path
            <input type="text" name="github_content_path" value="<?php echo wps_h($settings['github_content_path']); ?>">
        </label>

        <h3 class="full">Booking placeholders for later publishing</h3>

        <label class="full">
            Website booking link
            <input type="text" name="website_link" value="<?php echo wps_h($settings['website_link']); ?>">
        </label>

        <label class="full">
            TripAdvisor booking link
            <input type="text" name="tripadvisor_link" value="<?php echo wps_h($settings['tripadvisor_link']); ?>">
        </label>

        <label class="full">
            Viator booking link
            <input type="text" name="viator_link" value="<?php echo wps_h($settings['viator_link']); ?>">
        </label>

        <div class="full actions">
            <button type="submit" name="action" value="save_settings">Save Settings</button>
            <button type="submit" name="action" value="test_connection">Save & Test GitHub Connection</button>
        </div>
    </form>
</section>

<?php wps_render_footer(); ?>
