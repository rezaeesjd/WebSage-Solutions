<?php
declare(strict_types=1);

require __DIR__ . '/lib/settings.php';

$settings = load_site_settings();
$site = $settings['site'] ?? [];
$plugins = $settings['plugins'] ?? [];

function esc(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$siteTitle = $site['site_title'] ?? 'Websage Solutions Plugins | Websage Solutions';
$metaDescription = $site['meta_description'] ?? '';
$heroEyebrow = $site['hero_eyebrow'] ?? '';
$heroHeading = $site['hero_heading'] ?? 'WordPress helpers from Websage Solutions';
$heroBody = $site['hero_body'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= esc($siteTitle) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if ($metaDescription): ?>
        <meta name="description" content="<?= esc($metaDescription) ?>">
    <?php endif; ?>
    <style>
        :root {
            color-scheme: light dark;
            --bg: #0f172a;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #475569;
            --accent: #2563eb;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: 'Inter', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 35%, #0f172a 100%);
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        main {
            width: 100%;
            max-width: 960px;
        }
        header {
            position: relative;
            text-align: center;
            margin-bottom: 32px;
            padding-top: 56px;
        }
        header h1 {
            font-size: 2.4rem;
            margin: 0 0 12px;
        }
        header p {
            margin: 0;
            color: rgba(248, 250, 252, 0.8);
        }
        .lab-logo {
            position: absolute;
            top: 12px;
            left: 50%;
            transform: translateX(-50%);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            background: rgba(15, 23, 42, 0.4);
            border: 1px solid rgba(248, 250, 252, 0.25);
            color: #f8fafc;
            backdrop-filter: blur(6px);
        }
        .lab-logo-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #38bdf8;
            box-shadow: 0 0 10px rgba(56, 189, 248, 0.8);
        }
        .lab-logo:hover,
        .lab-logo:focus {
            color: #fff;
            border-color: rgba(248, 250, 252, 0.55);
        }
        .eyebrow {
            text-transform: uppercase;
            letter-spacing: 0.18em;
            font-size: 0.78rem;
            margin-bottom: 12px;
            color: rgba(248, 250, 252, 0.7);
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .card {
            background: var(--card);
            color: var(--text);
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.35);
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .card h2 {
            margin: 0;
            font-size: 1.4rem;
        }
        .card p {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
        }
        .card a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--accent);
            font-weight: 600;
            text-decoration: none;
        }
        .card a svg {
            width: 18px;
            height: 18px;
        }
        @media (max-width: 520px) {
            header {
                padding-top: 32px;
            }
            .lab-logo {
                position: static;
                transform: none;
                margin-bottom: 16px;
            }
        }
    </style>
</head>
<body>
    <main>
        <header>
            <a class="lab-logo" href="/" aria-label="Websage Solutions Lab homepage">
                <span class="lab-logo-dot" aria-hidden="true"></span>
                Websage Solutions Lab
            </a>
            <?php if ($heroEyebrow): ?><div class="eyebrow"><?= esc($heroEyebrow) ?></div><?php endif; ?>
            <h1><?= esc($heroHeading) ?></h1>
            <p><?= esc($heroBody) ?></p>
        </header>
        <div class="grid">
            <?php foreach ($plugins as $plugin): ?>
                <?php if (empty($plugin['slug'])) { continue; } ?>
                <article class="card">
                    <h2><?= esc($plugin['card_title'] ?? $plugin['slug']) ?></h2>
                    <p><?= esc($plugin['card_excerpt'] ?? '') ?></p>
                    <a href="/<?= esc($plugin['slug']) ?>/">
                        Visit page
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M7 17 17 7" />
                            <path d="M7 7h10v10" />
                        </svg>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
