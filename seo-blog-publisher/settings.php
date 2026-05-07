<?php
require_once __DIR__ . '/functions.php';

$settings = sbp_load_settings();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!sbp_verify_csrf()) {
        $error = 'Security check failed. Please refresh and try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'install') {
            $password = trim($_POST['password'] ?? '');
            $confirm = trim($_POST['confirm_password'] ?? '');

            if (strlen($password) < 8) {
                $error = 'Password must be at least 8 characters.';
            } elseif ($password !== $confirm) {
                $error = 'Passwords do not match.';
            } else {
                $settings['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
                $settings['installed'] = true;
                sbp_save_settings($settings);
                $_SESSION['sbp_logged_in'] = true;
                $success = 'Installation completed. You can now configure the archive.';
            }
        }

        if ($action === 'login') {
            $password = trim($_POST['password'] ?? '');

            if (!empty($settings['password_hash']) && password_verify($password, $settings['password_hash'])) {
                $_SESSION['sbp_logged_in'] = true;
                $success = 'Logged in.';
            } else {
                $error = 'Incorrect password.';
            }
        }

        if ($action === 'save_settings') {
            if (!sbp_is_logged_in()) {
                $error = 'Please log in first.';
            } else {
                $settings['site_name'] = trim($_POST['site_name'] ?? 'Milano Adventures');
                $settings['archive_title'] = trim($_POST['archive_title'] ?? 'Travel Guides & Tour Ideas');
                $settings['archive_description'] = trim($_POST['archive_description'] ?? '');
                $settings['archive_base_url'] = trim($_POST['archive_base_url'] ?? '');
                $settings['github_owner'] = trim($_POST['github_owner'] ?? '');
                $settings['github_repo'] = trim($_POST['github_repo'] ?? '');
                $settings['github_branch'] = trim($_POST['github_branch'] ?? 'main');
                $settings['github_content_path'] = trim($_POST['github_content_path'] ?? 'seo-content-system/tours');
                $settings['website_link'] = trim($_POST['website_link'] ?? '{{WebsiteLink}}');
                $settings['tripadvisor_link'] = trim($_POST['tripadvisor_link'] ?? '{{TripAdvisorLink}}');
                $settings['viator_link'] = trim($_POST['viator_link'] ?? '{{ViatorLink}}');
                $settings['default_status'] = ($_POST['default_status'] ?? 'draft') === 'published' ? 'published' : 'draft';

                if (sbp_save_settings($settings)) {
                    $success = 'Settings saved.';
                } else {
                    $error = 'Could not save settings. Make sure the data folder is writable.';
                }
            }
        }
    }

    $settings = sbp_load_settings();
}

sbp_render_header('Settings');
?>

<section class="panel">
    <h1>SEO Blog Publisher Settings</h1>
    <p class="muted">Configure the standalone archive shell. GitHub sync will be added in the next phase.</p>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo sbp_h($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo sbp_h($success); ?></div>
    <?php endif; ?>
</section>

<?php if (!sbp_is_installed()): ?>
    <section class="panel">
        <h2>First-time setup</h2>
        <p>Create an admin password for this standalone blog settings page.</p>
        <form method="post" class="form">
            <input type="hidden" name="csrf" value="<?php echo sbp_h(sbp_csrf_token()); ?>">
            <input type="hidden" name="action" value="install">

            <label>
                Password
                <input type="password" name="password" required minlength="8">
            </label>

            <label>
                Confirm password
                <input type="password" name="confirm_password" required minlength="8">
            </label>

            <button type="submit">Create Password</button>
        </form>
    </section>
<?php elseif (!sbp_is_logged_in()): ?>
    <section class="panel">
        <h2>Login</h2>
        <form method="post" class="form">
            <input type="hidden" name="csrf" value="<?php echo sbp_h(sbp_csrf_token()); ?>">
            <input type="hidden" name="action" value="login">

            <label>
                Password
                <input type="password" name="password" required>
            </label>

            <button type="submit">Login</button>
        </form>
    </section>
<?php else: ?>
    <section class="panel">
        <h2>Archive configuration</h2>
        <form method="post" class="form grid-form">
            <input type="hidden" name="csrf" value="<?php echo sbp_h(sbp_csrf_token()); ?>">
            <input type="hidden" name="action" value="save_settings">

            <label>
                Site name
                <input type="text" name="site_name" value="<?php echo sbp_h($settings['site_name']); ?>" required>
            </label>

            <label>
                Archive title
                <input type="text" name="archive_title" value="<?php echo sbp_h($settings['archive_title']); ?>" required>
            </label>

            <label class="full">
                Archive description
                <textarea name="archive_description" rows="3"><?php echo sbp_h($settings['archive_description']); ?></textarea>
            </label>

            <label class="full">
                Archive public URL or path
                <input type="text" name="archive_base_url" value="<?php echo sbp_h($settings['archive_base_url'] ?: sbp_current_url_base()); ?>" placeholder="https://www.example.com/blog/">
                <small>Use this to remember where this archive will live publicly.</small>
            </label>

            <h3 class="full">GitHub source settings for future sync</h3>

            <label>
                GitHub owner
                <input type="text" name="github_owner" value="<?php echo sbp_h($settings['github_owner']); ?>">
            </label>

            <label>
                GitHub repo
                <input type="text" name="github_repo" value="<?php echo sbp_h($settings['github_repo']); ?>">
            </label>

            <label>
                GitHub branch
                <input type="text" name="github_branch" value="<?php echo sbp_h($settings['github_branch']); ?>">
            </label>

            <label>
                GitHub content path
                <input type="text" name="github_content_path" value="<?php echo sbp_h($settings['github_content_path']); ?>">
            </label>

            <h3 class="full">Booking placeholders</h3>

            <label class="full">
                Website booking link
                <input type="text" name="website_link" value="<?php echo sbp_h($settings['website_link']); ?>">
            </label>

            <label class="full">
                TripAdvisor booking link
                <input type="text" name="tripadvisor_link" value="<?php echo sbp_h($settings['tripadvisor_link']); ?>">
            </label>

            <label class="full">
                Viator booking link
                <input type="text" name="viator_link" value="<?php echo sbp_h($settings['viator_link']); ?>">
            </label>

            <label>
                Default import status later
                <select name="default_status">
                    <option value="draft" <?php echo $settings['default_status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                    <option value="published" <?php echo $settings['default_status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                </select>
            </label>

            <div class="full actions">
                <button type="submit">Save Settings</button>
                <a class="button-secondary" href="index.php">View Archive</a>
            </div>
        </form>
    </section>
<?php endif; ?>

<?php sbp_render_footer(); ?>
