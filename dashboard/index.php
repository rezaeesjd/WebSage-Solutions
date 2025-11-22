<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/../lib/settings.php';

$settings = load_site_settings();
$siteSettings = $settings['site'] ?? [];
$pluginSettings = $settings['plugins'] ?? [];
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
            $incomingSite = $_POST['site'] ?? [];
            $sitePayload = [
                'site_title'       => trim($incomingSite['site_title'] ?? ''),
                'meta_description' => trim($incomingSite['meta_description'] ?? ''),
                'hero_eyebrow'     => trim($incomingSite['hero_eyebrow'] ?? ''),
                'hero_heading'     => trim($incomingSite['hero_heading'] ?? ''),
                'hero_body'        => trim($incomingSite['hero_body'] ?? ''),
                'contact_email'    => trim($incomingSite['contact_email'] ?? ''),
            ];

            $siteMissing = [];
            foreach (['site_title', 'hero_heading', 'hero_body'] as $requiredKey) {
                if ($sitePayload[$requiredKey] === '') {
                    $siteMissing[] = $requiredKey;
                }
            }

            $pluginsPayload = [];
            $pluginErrors = [];
            $incomingPlugins = $_POST['plugins'] ?? [];
            if (!is_array($incomingPlugins)) {
                $incomingPlugins = [];
            }

            foreach ($incomingPlugins as $slug => $pluginData) {
                if (!is_array($pluginData)) {
                    continue;
                }
                $slugKey = sanitize_slug((string)($pluginData['slug'] ?? $slug));
                if ($slugKey === '') {
                    $pluginErrors[] = 'Each plugin entry must include a slug (letters, numbers, and hyphens).';
                    continue;
                }

                $pluginPayload = [
                    'slug'             => $slugKey,
                    'card_title'       => trim($pluginData['card_title'] ?? ''),
                    'card_excerpt'     => trim($pluginData['card_excerpt'] ?? ''),
                    'site_title'       => trim($pluginData['site_title'] ?? ''),
                    'meta_description' => trim($pluginData['meta_description'] ?? ''),
                    'hero_eyebrow'     => trim($pluginData['hero_eyebrow'] ?? ''),
                    'hero_heading'     => trim($pluginData['hero_heading'] ?? ''),
                    'hero_body'        => trim($pluginData['hero_body'] ?? ''),
                    'download_label'   => trim($pluginData['download_label'] ?? 'Download Plugin'),
                    'github_label'     => trim($pluginData['github_label'] ?? 'View on GitHub'),
                    'cta_download_url' => sanitize_url($pluginData['cta_download_url'] ?? ''),
                    'cta_github_url'   => sanitize_url($pluginData['cta_github_url'] ?? ''),
                    'canonical_url'    => sanitize_url($pluginData['canonical_url'] ?? ''),
                    'og_image'         => sanitize_url($pluginData['og_image'] ?? ''),
                ];

                if ($pluginPayload['download_label'] === '') {
                    $pluginPayload['download_label'] = 'Download Plugin';
                }
                if ($pluginPayload['github_label'] === '') {
                    $pluginPayload['github_label'] = 'View on GitHub';
                }

                $requiredPluginKeys = ['card_title', 'card_excerpt', 'site_title', 'hero_heading', 'hero_body'];
                $missingPluginKeys = [];
                foreach ($requiredPluginKeys as $requiredKey) {
                    if ($pluginPayload[$requiredKey] === '') {
                        $missingPluginKeys[] = $requiredKey;
                    }
                }

                if ($pluginPayload['cta_download_url'] === '' || $pluginPayload['cta_github_url'] === '') {
                    $pluginErrors[] = sprintf('Plugin "%s" needs valid download and GitHub URLs.', $pluginPayload['card_title'] ?: $slugKey);
                } elseif (!empty($missingPluginKeys)) {
                    $pluginErrors[] = sprintf('Plugin "%s" is missing: %s', $pluginPayload['card_title'] ?: $slugKey, implode(', ', $missingPluginKeys));
                }

                $pluginsPayload[$slugKey] = $pluginPayload;
            }

            if (!empty($siteMissing)) {
                $flash['error'][] = 'Please fill out all required global fields (title and hero content).';
            } elseif (!empty($pluginErrors)) {
                foreach ($pluginErrors as $message) {
                    $flash['error'][] = $message;
                }
            } elseif (empty($pluginsPayload)) {
                $flash['error'][] = 'At least one plugin page must be configured.';
            } else {
                save_site_settings([
                    'site'    => $sitePayload,
                    'plugins' => $pluginsPayload,
                ]);
                $settings = load_site_settings();
                $siteSettings = $settings['site'] ?? [];
                $pluginSettings = $settings['plugins'] ?? [];
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
        input[type="email"],
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
            margin-top: 8px;
        }
        .help-text {
            color: var(--muted);
            margin: -6px 0 16px;
            font-size: 0.95rem;
        }
        .plugin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 18px;
            margin-bottom: 16px;
        }
        .plugin-card {
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 16px;
            background: #fff;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06);
        }
        .plugin-card textarea {
            min-height: 70px;
        }
        .plugin-card .remove-plugin {
            background: transparent;
            color: #b91c1c;
            border: 1px dashed rgba(185, 28, 28, 0.4);
            padding: 8px 14px;
            border-radius: 10px;
            margin-top: 8px;
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
        .links {
            padding: 0 24px 8px;
        }
        .links-head h2 {
            margin: 6px 0;
            font-size: 1.4rem;
        }
        .links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 12px;
            margin-top: 14px;
        }
        .link-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: inherit;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 14px 16px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.07);
        }
        .link-card span {
            font-size: 1.4rem;
            color: var(--accent);
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

    <?php
        $siteLinks = [];
        foreach ($pluginSettings as $plugin) {
            if (empty($plugin['slug'])) {
                continue;
            }
            $siteLinks[] = [
                'title' => $plugin['card_title'] ?? $plugin['slug'],
                'summary' => $plugin['card_excerpt'] ?? 'Plugin landing page',
                'url' => '/' . $plugin['slug'] . '/',
            ];
        }

        $siteLinks[] = [
            'title' => 'Metals Desk Prototype',
            'summary' => 'Front-end prototype comparing gold and silver across venues.',
            'url' => '/metals-trade-prototype/',
        ];
    ?>

    <section class="links">
        <div class="links-head">
            <div>
                <p class="eyebrow">Site Links</p>
                <h2>Direct access to live pages</h2>
                <p class="muted">Use these shortcuts to quickly open each plugin page plus the metals trading prototype.</p>
            </div>
        </div>
        <div class="links-grid">
            <?php foreach ($siteLinks as $link): ?>
                <a class="link-card" href="<?= h($link['url']) ?>">
                    <div>
                        <p class="eyebrow">Live URL</p>
                        <h3><?= h($link['title']) ?></h3>
                        <p class="muted"><?= h($link['summary']) ?></p>
                    </div>
                    <span aria-hidden="true">↗</span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <?php foreach ($flash['success'] as $message): ?>
        <div class="flash flash-success"><?= h($message) ?></div>
    <?php endforeach; ?>
    <?php foreach ($flash['error'] as $message): ?>
        <div class="flash flash-error"><?= h($message) ?></div>
    <?php endforeach; ?>

    <main>
        <section>
            <h2>Global Options</h2>
            <form method="post" id="settings-form">
                <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
                <input type="hidden" name="action" value="save_settings">
                <div class="field">
                    <label for="site_title">Website Title</label>
                    <input type="text" id="site_title" name="site[site_title]" value="<?= h($siteSettings['site_title'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label for="meta_description">Meta Description</label>
                    <textarea id="meta_description" name="site[meta_description]"><?= h($siteSettings['meta_description'] ?? '') ?></textarea>
                </div>
                <div class="field">
                    <label for="hero_eyebrow">Header Eyebrow</label>
                    <input type="text" id="hero_eyebrow" name="site[hero_eyebrow]" value="<?= h($siteSettings['hero_eyebrow'] ?? '') ?>">
                </div>
                <div class="field">
                    <label for="hero_heading">Hero Heading</label>
                    <input type="text" id="hero_heading" name="site[hero_heading]" value="<?= h($siteSettings['hero_heading'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label for="hero_body">Hero Body</label>
                    <textarea id="hero_body" name="site[hero_body]" required><?= h($siteSettings['hero_body'] ?? '') ?></textarea>
                </div>
                <div class="field">
                    <label for="contact_email">Contact Email</label>
                    <input type="email" id="contact_email" name="site[contact_email]" value="<?= h($siteSettings['contact_email'] ?? '') ?>">
                </div>

                <h3>Plugin Landing Pages</h3>
                <p class="help-text">Manage hero copy, metadata, and download links for each plugin page.</p>
                <div class="plugin-grid" id="plugin-grid">
                    <?php foreach ($pluginSettings as $slug => $plugin): ?>
                        <article class="plugin-card" data-slug="<?= h($slug) ?>">
                            <div class="field">
                                <label>Plugin Slug</label>
                                <input type="text" name="plugins[<?= h($slug) ?>][slug]" value="<?= h($plugin['slug'] ?? $slug) ?>" required>
                            </div>
                            <div class="field">
                                <label>Homepage Card Title</label>
                                <input type="text" name="plugins[<?= h($slug) ?>][card_title]" value="<?= h($plugin['card_title'] ?? '') ?>" required>
                            </div>
                            <div class="field">
                                <label>Homepage Card Summary</label>
                                <textarea name="plugins[<?= h($slug) ?>][card_excerpt]" required><?= h($plugin['card_excerpt'] ?? '') ?></textarea>
                            </div>
                            <div class="field">
                                <label>Page Title</label>
                                <input type="text" name="plugins[<?= h($slug) ?>][site_title]" value="<?= h($plugin['site_title'] ?? '') ?>" required>
                            </div>
                            <div class="field">
                                <label>Meta Description</label>
                                <textarea name="plugins[<?= h($slug) ?>][meta_description]"><?= h($plugin['meta_description'] ?? '') ?></textarea>
                            </div>
                            <div class="field">
                                <label>Hero Eyebrow</label>
                                <input type="text" name="plugins[<?= h($slug) ?>][hero_eyebrow]" value="<?= h($plugin['hero_eyebrow'] ?? '') ?>">
                            </div>
                            <div class="field">
                                <label>Hero Heading</label>
                                <input type="text" name="plugins[<?= h($slug) ?>][hero_heading]" value="<?= h($plugin['hero_heading'] ?? '') ?>" required>
                            </div>
                            <div class="field">
                                <label>Hero Body</label>
                                <textarea name="plugins[<?= h($slug) ?>][hero_body]" required><?= h($plugin['hero_body'] ?? '') ?></textarea>
                            </div>
                            <div class="field">
                                <label>Download Button Label</label>
                                <input type="text" name="plugins[<?= h($slug) ?>][download_label]" value="<?= h($plugin['download_label'] ?? '') ?>">
                            </div>
                            <div class="field">
                                <label>Download URL</label>
                                <input type="url" name="plugins[<?= h($slug) ?>][cta_download_url]" value="<?= h($plugin['cta_download_url'] ?? '') ?>" required>
                            </div>
                            <div class="field">
                                <label>GitHub Button Label</label>
                                <input type="text" name="plugins[<?= h($slug) ?>][github_label]" value="<?= h($plugin['github_label'] ?? '') ?>">
                            </div>
                            <div class="field">
                                <label>GitHub Repository URL</label>
                                <input type="url" name="plugins[<?= h($slug) ?>][cta_github_url]" value="<?= h($plugin['cta_github_url'] ?? '') ?>" required>
                            </div>
                            <div class="field">
                                <label>Canonical URL</label>
                                <input type="url" name="plugins[<?= h($slug) ?>][canonical_url]" value="<?= h($plugin['canonical_url'] ?? '') ?>">
                            </div>
                            <div class="field">
                                <label>OG Image URL</label>
                                <input type="url" name="plugins[<?= h($slug) ?>][og_image]" value="<?= h($plugin['og_image'] ?? '') ?>">
                            </div>
                            <button type="button" class="remove-plugin">Remove Plugin</button>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="button-row">
                    <button type="button" class="btn-secondary" id="add-plugin">Add Plugin</button>
                    <button type="submit" class="btn-primary">Save Settings</button>
                </div>
            </form>
            <template id="plugin-template">
                <article class="plugin-card">
                    <div class="field">
                        <label>Plugin Slug</label>
                        <input type="text" data-field="slug" required>
                    </div>
                    <div class="field">
                        <label>Homepage Card Title</label>
                        <input type="text" data-field="card_title" required>
                    </div>
                    <div class="field">
                        <label>Homepage Card Summary</label>
                        <textarea data-field="card_excerpt" required></textarea>
                    </div>
                    <div class="field">
                        <label>Page Title</label>
                        <input type="text" data-field="site_title" required>
                    </div>
                    <div class="field">
                        <label>Meta Description</label>
                        <textarea data-field="meta_description"></textarea>
                    </div>
                    <div class="field">
                        <label>Hero Eyebrow</label>
                        <input type="text" data-field="hero_eyebrow">
                    </div>
                    <div class="field">
                        <label>Hero Heading</label>
                        <input type="text" data-field="hero_heading" required>
                    </div>
                    <div class="field">
                        <label>Hero Body</label>
                        <textarea data-field="hero_body" required></textarea>
                    </div>
                    <div class="field">
                        <label>Download Button Label</label>
                        <input type="text" data-field="download_label">
                    </div>
                    <div class="field">
                        <label>Download URL</label>
                        <input type="url" data-field="cta_download_url" required>
                    </div>
                    <div class="field">
                        <label>GitHub Button Label</label>
                        <input type="text" data-field="github_label">
                    </div>
                    <div class="field">
                        <label>GitHub Repository URL</label>
                        <input type="url" data-field="cta_github_url" required>
                    </div>
                    <div class="field">
                        <label>Canonical URL</label>
                        <input type="url" data-field="canonical_url">
                    </div>
                    <div class="field">
                        <label>OG Image URL</label>
                        <input type="url" data-field="og_image">
                    </div>
                    <button type="button" class="remove-plugin">Remove Plugin</button>
                </article>
            </template>
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
    <script>
        (function () {
            const addButton = document.getElementById('add-plugin');
            const grid = document.getElementById('plugin-grid');
            const template = document.getElementById('plugin-template');
            if (!addButton || !grid || !template) {
                return;
            }

            let counter = grid.children.length;
            addButton.addEventListener('click', function () {
                counter += 1;
                const fragment = template.content.cloneNode(true);
                fragment.querySelectorAll('[data-field]').forEach(function (input) {
                    const field = input.getAttribute('data-field');
                    const name = `plugins[new-${counter}][${field}]`;
                    input.setAttribute('name', name);
                    if (typeof input.value !== 'undefined') {
                        input.value = '';
                    }
                });
                grid.appendChild(fragment);
            });

            grid.addEventListener('click', function (event) {
                const removeButton = event.target.closest('.remove-plugin');
                if (!removeButton) {
                    return;
                }
                const card = removeButton.closest('.plugin-card');
                if (!card) {
                    return;
                }
                if (grid.children.length <= 1) {
                    alert('At least one plugin entry is required.');
                    return;
                }
                card.remove();
            });
        })();
    </script>
</body>
</html>
