<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/github.php';

function wps_fetch_github_file_text(array $settings, string $path): array
{
    $url = wps_github_api_url($settings, $path);
    $response = wps_github_fetch_json($url);

    if (!$response['ok']) {
        return ['ok' => false, 'content' => '', 'error' => $response['error']];
    }

    $data = $response['data'];
    if (!is_array($data) || empty($data['type']) || $data['type'] !== 'file') {
        return ['ok' => false, 'content' => '', 'error' => 'GitHub item is not a file.'];
    }

    if (!empty($data['content']) && ($data['encoding'] ?? '') === 'base64') {
        $decoded = base64_decode(str_replace("\n", '', $data['content']), true);
        if ($decoded === false) {
            return ['ok' => false, 'content' => '', 'error' => 'Could not decode GitHub file content.'];
        }
        return ['ok' => true, 'content' => $decoded, 'error' => ''];
    }

    if (!empty($data['download_url'])) {
        $raw = wps_github_fetch_raw($data['download_url']);
        return $raw;
    }

    return ['ok' => false, 'content' => '', 'error' => 'No readable content returned by GitHub.'];
}

function wps_github_fetch_raw(string $url): array
{
    $headers = [
        'User-Agent: WebPublisherSystem',
        'Accept: text/plain',
    ];

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $body = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($body === false || $error) {
            return ['ok' => false, 'content' => '', 'error' => $error ?: 'Raw download failed.'];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            return ['ok' => false, 'content' => '', 'error' => 'Raw download returned HTTP ' . $httpCode];
        }

        return ['ok' => true, 'content' => $body, 'error' => ''];
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => implode("\r\n", $headers),
            'timeout' => 20,
        ],
    ]);

    $body = @file_get_contents($url, false, $context);
    if ($body === false) {
        return ['ok' => false, 'content' => '', 'error' => 'Raw download failed.'];
    }

    return ['ok' => true, 'content' => $body, 'error' => ''];
}

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
    $connection = wps_test_github_connection($settings);
    if (!$connection['ok']) {
        return ['ok' => false, 'error' => $connection['message'], 'folders' => []];
    }

    $folders = [];
    foreach ($connection['items'] as $item) {
        if (($item['type'] ?? '') === 'dir') {
            $folders[] = $item;
        }
    }

    return ['ok' => true, 'error' => '', 'folders' => $folders];
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

        $metaFile = wps_fetch_github_file_text($settings, $folderPath . '/meta.json');
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

    $blog = wps_fetch_github_file_text($settings, $folderPath . '/blog-post.md');
    $faq = wps_fetch_github_file_text($settings, $folderPath . '/faq.md');

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
