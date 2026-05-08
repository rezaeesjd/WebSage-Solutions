<?php
require_once __DIR__ . '/functions.php';

const WPS_POST_OVERRIDES_DIR = __DIR__ . '/data/post-overrides';

function wps_post_safe_slug(string $slug): string
{
    $slug = trim($slug);
    return preg_match('/^[a-zA-Z0-9_-]+$/', $slug) ? $slug : '';
}

function wps_post_override_path(string $slug): string
{
    return WPS_POST_OVERRIDES_DIR . '/' . wps_post_safe_slug($slug) . '.json';
}

function wps_ensure_post_overrides_dir(): void
{
    if (!is_dir(WPS_POST_OVERRIDES_DIR)) {
        mkdir(WPS_POST_OVERRIDES_DIR, 0755, true);
    }
}

function wps_load_post_override(string $slug): array
{
    $safeSlug = wps_post_safe_slug($slug);
    if ($safeSlug === '') {
        return [];
    }

    $path = wps_post_override_path($safeSlug);
    if (!is_file($path)) {
        return [];
    }

    $json = file_get_contents($path);
    $data = json_decode((string) $json, true);

    return is_array($data) ? $data : [];
}

function wps_save_post_override(string $slug, array $override): bool
{
    $safeSlug = wps_post_safe_slug($slug);
    if ($safeSlug === '') {
        return false;
    }

    wps_ensure_post_overrides_dir();
    $override['slug'] = $safeSlug;
    $override['updated_at'] = gmdate('c');

    return file_put_contents(
        wps_post_override_path($safeSlug),
        json_encode($override, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    ) !== false;
}

function wps_apply_post_override(array $post): array
{
    $override = wps_load_post_override((string) ($post['slug'] ?? ''));
    if (!$override) {
        $post['has_local_edits'] = false;
        return $post;
    }

    foreach (['title', 'meta_description', 'primary_keyword', 'funnel_stage', 'product_reference_code'] as $field) {
        if (array_key_exists($field, $override)) {
            $post[$field] = (string) $override[$field];
        }
    }

    $post['has_local_edits'] = true;
    $post['local_override'] = $override;

    return $post;
}
