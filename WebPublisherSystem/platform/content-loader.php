<?php
require_once __DIR__ . '/functions.php';

const WPS_LOCAL_CONTENT_DIR = __DIR__ . '/../content-system/tours';

function wps_replace_placeholders(string $content, array $settings): string
{
    return str_replace(
        ['{{WebsiteLink}}', '{{TripAdvisorLink}}', '{{ViatorLink}}'],
        [$settings['website_link'] ?? '{{WebsiteLink}}', $settings['tripadvisor_link'] ?? '{{TripAdvisorLink}}', $settings['viator_link'] ?? '{{ViatorLink}}'],
        $content
    );
}

function wps_get_content_folders(array $settings): array
{
    $baseDir = realpath(WPS_LOCAL_CONTENT_DIR);

    if ($baseDir === false || !is_dir($baseDir)) {
        return [
            'ok' => false,
            'error' => 'Local content folder not found: WebPublisherSystem/content-system/tours. Upload or sync the content-system folder first.',
            'folders' => [],
        ];
    }

    $folders = [];
    $items = scandir($baseDir);

    if ($items === false) {
        return ['ok' => false, 'error' => 'Could not read local content folder.', 'folders' => []];
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $fullPath = $baseDir . '/' . $item;
        if (!is_dir($fullPath)) {
            continue;
        }

        $folders[] = [
            'name' => $item,
            'path' => $fullPath,
            'relative_path' => 'content-system/tours/' . $item,
            'type' => 'dir',
        ];
    }

    return ['ok' => true, 'error' => '', 'folders' => $folders];
}

function wps_read_local_file(string $path): array
{
    $baseDir = realpath(WPS_LOCAL_CONTENT_DIR);
    $realPath = realpath($path);

    if ($baseDir === false || $realPath === false || !str_starts_with($realPath, $baseDir)) {
        return ['ok' => false, 'content' => '', 'error' => 'Unsafe or missing local file path.'];
    }

    if (!is_file($realPath)) {
        return ['ok' => false, 'content' => '', 'error' => 'Local file not found.'];
    }

    $content = file_get_contents($realPath);
    if ($content === false) {
        return ['ok' => false, 'content' => '', 'error' => 'Could not read local file.'];
    }

    return ['ok' => true, 'content' => $content, 'error' => ''];
}

function wps_get_posts(array $settings): array
{
    $foldersResult = wps_get_content_folders($settings);
    if (!$foldersResult['ok']) {
        return ['ok' => false, 'error' => $foldersResult['error'], 'posts' => []];
    }

    $posts = [];

    foreach ($foldersResult['folders'] as $folder) {
        $folderPath = $folder['path'] ?? '';
        $folderName = $folder['name'] ?? '';

        if (!$folderPath || !$folderName) {
            continue;
        }

        $metaFile = wps_read_local_file($folderPath . '/meta.json');
        if (!$metaFile['ok']) {
            continue;
        }

        $meta = json_decode($metaFile['content'], true);
        if (!is_array($meta)) {
            continue;
        }

        $slug = $meta['slug'] ?? $folderName;
        $posts[] = [
            'folder_name' => $folderName,
            'folder_path' => $folderPath,
            'slug' => $slug,
            'title' => $meta['page_title'] ?? $meta['tour_title'] ?? ucwords(str_replace('-', ' ', $folderName)),
            'meta_description' => $meta['meta_description'] ?? '',
            'primary_keyword' => $meta['primary_keyword'] ?? '',
            'funnel_stage' => $meta['funnel_stage'] ?? '',
            'product_reference_code' => $meta['product_reference_code'] ?? '',
            'brand' => $meta['brand'] ?? ($settings['site_name'] ?? ''),
            'meta' => $meta,
        ];
    }

    usort($posts, fn($a, $b) => strcmp($a['title'], $b['title']));

    return ['ok' => true, 'error' => '', 'posts' => $posts];
}

function wps_find_post_by_slug(array $settings, string $slug): array
{
    $postsResult = wps_get_posts($settings);
    if (!$postsResult['ok']) {
        return ['ok' => false, 'error' => $postsResult['error'], 'post' => null];
    }

    foreach ($postsResult['posts'] as $post) {
        if (($post['slug'] ?? '') === $slug || ($post['folder_name'] ?? '') === $slug) {
            return ['ok' => true, 'error' => '', 'post' => $post];
        }
    }

    return ['ok' => false, 'error' => 'Post not found.', 'post' => null];
}

function wps_get_post_content(array $settings, array $post): array
{
    $folderPath = $post['folder_path'] ?? '';
    if (!$folderPath) {
        return ['ok' => false, 'error' => 'Missing post folder path.', 'blog' => '', 'faq' => ''];
    }

    $blog = wps_read_local_file($folderPath . '/blog-post.md');
    $faq = wps_read_local_file($folderPath . '/faq.md');

    if (!$blog['ok']) {
        return ['ok' => false, 'error' => $blog['error'], 'blog' => '', 'faq' => ''];
    }

    return [
        'ok' => true,
        'error' => '',
        'blog' => wps_replace_placeholders($blog['content'], $settings),
        'faq' => $faq['ok'] ? wps_replace_placeholders($faq['content'], $settings) : '',
    ];
}

function wps_markdown_to_html(string $markdown): string
{
    $lines = preg_split('/\r\n|\r|\n/', $markdown);
    $html = '';
    $paragraph = [];
    $inList = false;

    $flushParagraph = function () use (&$html, &$paragraph): void {
        if (!$paragraph) {
            return;
        }
        $text = implode(' ', $paragraph);
        $html .= '<p>' . wps_inline_markdown($text) . '</p>' . "\n";
        $paragraph = [];
    };

    $closeList = function () use (&$html, &$inList): void {
        if ($inList) {
            $html .= "</ul>\n";
            $inList = false;
        }
    };

    foreach ($lines as $line) {
        $trim = trim($line);

        if ($trim === '') {
            $flushParagraph();
            $closeList();
            continue;
        }

        if (preg_match('/^(#{1,6})\s+(.+)$/', $trim, $matches)) {
            $flushParagraph();
            $closeList();
            $level = min(strlen($matches[1]), 4);
            $text = wps_inline_markdown($matches[2]);
            $html .= '<h' . $level . '>' . $text . '</h' . $level . '>' . "\n";
            continue;
        }

        if (preg_match('/^-\s+(.+)$/', $trim, $matches)) {
            $flushParagraph();
            if (!$inList) {
                $html .= "<ul>\n";
                $inList = true;
            }
            $html .= '<li>' . wps_inline_markdown($matches[1]) . '</li>' . "\n";
            continue;
        }

        $paragraph[] = $trim;
    }

    $flushParagraph();
    $closeList();

    return $html;
}

function wps_inline_markdown(string $text): string
{
    $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $escaped = preg_replace('/`([^`]+)`/', '<code>$1</code>', $escaped);
    $escaped = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $escaped);
    $escaped = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="$2">$1</a>', $escaped);

    return $escaped;
}
