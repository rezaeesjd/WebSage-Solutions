<?php
require_once __DIR__ . '/functions.php';
$settings = sbp_load_settings();
sbp_render_header($settings['archive_title']);
?>

<section class="hero panel">
    <p class="eyebrow">Milano Adventures Blog</p>
    <h1><?php echo sbp_h($settings['archive_title']); ?></h1>
    <p><?php echo sbp_h($settings['archive_description']); ?></p>
</section>

<section class="panel">
    <h2>Archive status</h2>
    <p>This standalone archive shell is installed and ready for configuration.</p>

    <?php if (!sbp_is_installed()): ?>
        <div class="alert alert-warning">
            The settings page has not been configured yet.
            <a href="settings.php">Start setup</a>.
        </div>
    <?php else: ?>
        <div class="archive-card">
            <h3>Next step: connect content</h3>
            <p>In the next phase, this archive will sync generated blog posts from GitHub and list them here.</p>
            <a class="button-secondary" href="settings.php">Open Settings</a>
        </div>
    <?php endif; ?>
</section>

<section class="panel muted-panel">
    <h2>Planned archive layout</h2>
    <div class="post-grid">
        <article class="post-card">
            <p class="post-label">Example layout</p>
            <h3>Cinque Terre Tour from Milan</h3>
            <p>An example blog card will appear here after GitHub sync is added.</p>
            <span class="read-more">Read guide →</span>
        </article>
        <article class="post-card">
            <p class="post-label">Example layout</p>
            <h3>Milan Day Trips Guide</h3>
            <p>Future generated posts will be displayed in a clean archive format.</p>
            <span class="read-more">Read guide →</span>
        </article>
    </div>
</section>

<?php sbp_render_footer(); ?>
