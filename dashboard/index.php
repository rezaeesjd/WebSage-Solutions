<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/../lib/settings.php';

$settings = load_site_settings();
$deployConfigPath = __DIR__ . '/../config/deploy-config.php';
$deployConfig = file_exists($deployConfigPath) ? require $deployConfigPath : [];
$secretToken = $deployConfig['secret_token'] ?? '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

$flash = [
    'success' => [],
    'error' => [],
];
$deployResponse = null;

function sanitize_url(string $url): string
{
    $trimmed = trim($url);
    if ($trimmed === '') {
        return '';
    }
    return filter_var($trimmed, FILTER_VALIDATE_URL) ? $trimmed : '';
}

function trigger_deploy(string $token, string $repoKey = '', string $ref = ''): array
{
    if ($token === '') {
        return ['ok' => false, 'status' => 0, 'body' => '', 'error' => 'Secret token missing from config.'];
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $url = $scheme . '://' . $host . '/deploy.php';

    $payload = [
        'token' => $token,
    ];
    if ($repoKey !== '') {
        $payload['repo'] = $repoKey;
    }
    if ($ref !== '') {
        $payload['ref'] = $ref;
    }

    $body = '';
    $status = 0;
    $error = '';

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        $body = (string)curl_exec($ch);
        if ($body === false) {
            $error = curl_error($ch);
        }
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    } else {
        $context = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($payload),
                'timeout' => 60,
            ],
        ]);
        $result = @file_get_contents($url, false, $context);
        if ($result === false) {
            $error = 'Unable to reach deploy.php via HTTP.';
        } else {
            $body = (string)$result;
            if (isset($http_response_header) && is_array($http_response_header)) {
                foreach ($http_response_header as $headerLine) {
                    if (preg_match('#HTTP/\d+\.\d+\s+(\d{3})#', $headerLine, $matches)) {
                        $status = (int)$matches[1];
                        break;
                    }
                }
            }
        }
    }

    $ok = ($status >= 200 && $status < 300);
    if ($status === 0 && $error === '') {
        $error = 'Unknown deployment error.';
    }

    return [
        'ok' => $ok,
        'status' => $status,
        'body' => $body,
        'error' => $ok ? '' : $error,
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($csrfToken, $token)) {
        $flash['error'][] = 'Session expired. Refresh and try again.';
    } else {
        if ($action === 'save_settings') {
            $updated = [
                'site_title'       => $_POST['site_title'] ?? '',
                'meta_description' => $_POST['meta_description'] ?? '',
                'hero_eyebrow'     => $_POST['hero_eyebrow'] ?? '',
                'hero_heading'     => $_POST['hero_heading'] ?? '',
                'hero_body'        => $_POST['hero_body'] ?? '',
                'download_label'   => $_POST['download_label'] ?? '',
                'github_label'     => $_POST['github_label'] ?? '',
                'cta_download_url' => sanitize_url($_POST['cta_download_url'] ?? ''),
                'cta_github_url'   => sanitize_url($_POST['cta_github_url'] ?? ''),
                'canonical_url'    => sanitize_url($_POST['canonical_url'] ?? ''),
                'og_image'         => sanitize_url($_POST['og_image'] ?? ''),
                'contact_email'    => trim($_POST['contact_email'] ?? ''),
            ];

            $missing = [];
            foreach (['site_title', 'hero_heading', 'hero_body'] as $requiredKey) {
                if ($updated[$requiredKey] === '') {
                    $missing[] = $requiredKey;
                }
            }
            if ($updated['cta_download_url'] === '' || $updated['cta_github_url'] === '') {
                $flash['error'][] = 'Both Download URL and GitHub URL must be valid links.';
            } elseif (!empty($missing)) {
                $flash['error'][] = 'Please fill out all required fields.';
            } else {
                save_site_settings($updated);
                $settings = load_site_settings();
                $flash['success'][] = 'Settings saved.';
            }
        } elseif ($action === 'deploy_now') {
            $repoKey = trim($_POST['repo_key'] ?? '');
            $ref = trim($_POST['ref'] ?? '');
            $deployResponse = trigger_deploy($secretToken, $repoKey, $ref);
            if ($deployResponse['ok']) {
                $flash['success'][] = 'Deployment triggered successfully.';
            } else {
                $flash['error'][] = $deployResponse['error'] ?: 'Deployment failed.';
            }
        }
    }
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Site Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color-scheme: light dark;
            --bg: #f4f6fb;
            --card-bg: #ffffff;
            --border: #e2e8f0;
            --text: #0f172a;
            --muted: #475569;
            --accent: #2563eb;
            --accent-dark: #1d4ed8;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }
        header {
            padding: 32px 24px 16px;
        }
        header h1 {
            margin: 0;
            font-size: 2rem;
        }
        header p {
            margin: 6px 0 0;
            color: var(--muted);
        }
        main {
            padding: 0 24px 48px;
            max-width: 1100px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }
        section {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 20px 45px rgba(15,23,42,0.08);
        }
        section h2 {
            margin-top: 0;
            font-size: 1.3rem;
        }
        form .field {
            margin-bottom: 16px;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
        }
        input[type="text"],
        input[type="url"],
        textarea {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid var(--border);
            font-size: 1rem;
            font-family: inherit;
            resize: vertical;
        }
        textarea {
            min-height: 120px;
        }
        .button-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        button {
            border: none;
            border-radius: 999px;
            padding: 12px 22px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-primary {
            background: var(--accent);
            color: #fff;
        }
        .btn-primary:hover {
            background: var(--accent-dark);
        }
        .btn-secondary {
            background: #0f172a;
            color: #fff;
        }
        .flash {
            margin: 16px 24px 0;
            padding: 14px 18px;
            border-radius: 12px;
            font-weight: 600;
        }
        .flash-success {
            background: rgba(34,197,94,0.15);
            color: #059669;
        }
        .flash-error {
            background: rgba(248,113,113,0.2);
            color: #b91c1c;
        }
        pre {
            background: #0f172a;
            color: #e2e8f0;
            padding: 16px;
            border-radius: 12px;
            overflow-x: auto;
            font-size: 0.9rem;
        }
        @media (max-width: 900px) {
            main {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Site Dashboard</h1>
        <p>Adjust marketing copy, download/GitHub URLs, and trigger deployments without touching code.</p>
    </header>

    <?php foreach ($flash['success'] as $message): ?>
        <div class="flash flash-success"><?= h($message) ?></div>
    <?php endforeach; ?>
    <?php foreach ($flash['error'] as $message): ?>
        <div class="flash flash-error"><?= h($message) ?></div>
    <?php endforeach; ?>

    <main>
        <section>
            <h2>Global Options</h2>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
                <input type="hidden" name="action" value="save_settings">
                <div class="field">
                    <label for="site_title">Website Title</label>
                    <input type="text" id="site_title" name="site_title" value="<?= h($settings['site_title']) ?>" required>
                </div>
                <div class="field">
                    <label for="meta_description">Meta Description</label>
                    <textarea id="meta_description" name="meta_description"><?= h($settings['meta_description']) ?></textarea>
                </div>
                <div class="field">
                    <label for="hero_eyebrow">Header Eyebrow</label>
                    <input type="text" id="hero_eyebrow" name="hero_eyebrow" value="<?= h($settings['hero_eyebrow']) ?>">
                </div>
                <div class="field">
                    <label for="hero_heading">Hero Heading</label>
                    <input type="text" id="hero_heading" name="hero_heading" value="<?= h($settings['hero_heading']) ?>" required>
                </div>
                <div class="field">
                    <label for="hero_body">Hero Body</label>
                    <textarea id="hero_body" name="hero_body" required><?= h($settings['hero_body']) ?></textarea>
                </div>
                <div class="field">
                    <label for="download_label">Download Button Label</label>
                    <input type="text" id="download_label" name="download_label" value="<?= h($settings['download_label']) ?>">
                </div>
                <div class="field">
                    <label for="cta_download_url">Download URL</label>
                    <input type="url" id="cta_download_url" name="cta_download_url" value="<?= h($settings['cta_download_url']) ?>" required>
                </div>
                <div class="field">
                    <label for="github_label">GitHub Button Label</label>
                    <input type="text" id="github_label" name="github_label" value="<?= h($settings['github_label']) ?>">
                </div>
                <div class="field">
                    <label for="cta_github_url">GitHub Repository URL</label>
                    <input type="url" id="cta_github_url" name="cta_github_url" value="<?= h($settings['cta_github_url']) ?>" required>
                </div>
                <div class="field">
                    <label for="canonical_url">Canonical URL</label>
                    <input type="url" id="canonical_url" name="canonical_url" value="<?= h($settings['canonical_url']) ?>">
                </div>
                <div class="field">
                    <label for="og_image">OG Image URL</label>
                    <input type="url" id="og_image" name="og_image" value="<?= h($settings['og_image']) ?>">
                </div>
                <div class="field">
                    <label for="contact_email">Contact Email</label>
                    <input type="text" id="contact_email" name="contact_email" value="<?= h($settings['contact_email']) ?>">
                </div>
                <div class="button-row">
                    <button type="submit" class="btn-primary">Save Settings</button>
                </div>
            </form>
        </section>

        <section>
            <h2>Deployment</h2>
            <p><strong>Default Repo:</strong> <?= h(($deployConfig['default_owner'] ?? '') . '/' . ($deployConfig['default_repo'] ?? '')) ?> @ <?= h($deployConfig['default_ref'] ?? 'main') ?></p>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
                <input type="hidden" name="action" value="deploy_now">
                <div class="field">
                    <label for="repo_key">Alt Repo Key (optional)</label>
                    <input type="text" id="repo_key" name="repo_key" placeholder="e.g. github-updater">
                </div>
                <div class="field">
                    <label for="ref">Branch / Tag (optional)</label>
                    <input type="text" id="ref" name="ref" placeholder="main">
                </div>
                <div class="button-row">
                    <button type="submit" class="btn-secondary">Trigger Deploy</button>
                </div>
            </form>
            <?php if ($deployResponse): ?>
                <h3>Response (HTTP <?= (int)$deployResponse['status'] ?>)</h3>
                <pre><?= h($deployResponse['body'] ?: $deployResponse['error']) ?></pre>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
