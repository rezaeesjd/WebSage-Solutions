<?php
const WPS_ASSET_BASE = '../platform';
const WPS_ARCHIVE_URL = '../blog/';
const WPS_SETTINGS_URL = '../platform/settings.php';

require_once __DIR__ . '/../platform/content-loader.php';

$settings = wps_load_settings();
$slug = trim($_GET['slug'] ?? '');
$postResult = $slug ? wps_find_post_by_slug($settings, $slug) : ['ok' => false, 'error' => 'Missing post slug.', 'post' => null];
$post = $postResult['post'] ?? null;
$contentResult = $post ? wps_get_post_content($settings, $post) : ['ok' => false, 'error' => $postResult['error'] ?? 'Post not found.', 'blog' => '', 'faq' => ''];

$pageTitle = $post['title'] ?? 'Blog Post';
wps_render_header($pageTitle);
?>

<?php if (!$postResult['ok'] || !$post || !$contentResult['ok']): ?>
    <section class="panel">
        <h1>Post not available</h1>
        <div class="alert alert-error">
            <?php echo wps_h($contentResult['error'] ?: ($postResult['error'] ?? 'Post not found.')); ?>
        </div>
        <a class="button-secondary" href="./">Back to Blog Archive</a>
    </section>
<?php else: ?>
    <article class="panel blog-post">
        <p class="eyebrow"><?php echo wps_h($post['primary_keyword'] ?: 'Travel guide'); ?></p>
        <h1><?php echo wps_h($post['title']); ?></h1>
        <?php if (!empty($post['meta_description'])): ?>
            <p class="lead"><?php echo wps_h($post['meta_description']); ?></p>
        <?php endif; ?>
        <div class="post-meta">
            <?php if (!empty($post['funnel_stage'])): ?>
                <span><?php echo wps_h($post['funnel_stage']); ?></span>
            <?php endif; ?>
            <?php if (!empty($post['product_reference_code'])): ?>
                <span>Ref <?php echo wps_h($post['product_reference_code']); ?></span>
            <?php endif; ?>
        </div>

        <div class="content-body">
            <?php echo wps_markdown_to_html($contentResult['blog']); ?>
        </div>
    </article>

    <?php if (!empty(trim($contentResult['faq']))): ?>
        <section class="panel blog-faq">
            <div class="content-body">
                <?php echo wps_markdown_to_html($contentResult['faq']); ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="panel muted-panel">
        <a class="button-secondary" href="./">← Back to Blog Archive</a>
    </section>
<?php endif; ?>

<?php wps_render_footer(); ?>
