<?php
declare(strict_types=1);

require __DIR__ . '/../lib/settings.php';
$settings = load_site_settings();

function esc(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$siteTitle       = $settings['site_title'] ?? 'GitHub Plugin Installer & Updater | Websage Solutions';
$metaDescription = $settings['meta_description'] ?? '';
$heroEyebrow     = $settings['hero_eyebrow'] ?? '';
$heroHeading     = $settings['hero_heading'] ?? '';
$heroBody        = $settings['hero_body'] ?? '';
$downloadLabel   = $settings['download_label'] ?? 'Download Plugin';
$githubLabel     = $settings['github_label'] ?? 'View on GitHub';
$downloadUrl     = $settings['cta_download_url'] ?? '#';
$githubUrl       = $settings['cta_github_url'] ?? '#';
$canonicalUrl    = $settings['canonical_url'] ?? '';
$ogImage         = $settings['og_image'] ?? '';
$contactEmail    = $settings['contact_email'] ?? 'lab@websagesolutions.com';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= esc($siteTitle) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= esc($metaDescription) ?>">
    <?php if ($canonicalUrl): ?><link rel="canonical" href="<?= esc($canonicalUrl) ?>"><?php endif; ?>
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= esc($siteTitle) ?>">
    <meta property="og:description" content="<?= esc($metaDescription) ?>">
    <?php if ($canonicalUrl): ?><meta property="og:url" content="<?= esc($canonicalUrl) ?>"><?php endif; ?>
    <?php if ($ogImage): ?><meta property="og:image" content="<?= esc($ogImage) ?>"><?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= esc($siteTitle) ?>">
    <meta name="twitter:description" content="<?= esc($metaDescription) ?>">
    <?php if ($ogImage): ?><meta name="twitter:image" content="<?= esc($ogImage) ?>"><?php endif; ?>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <style>
        :root {
            color-scheme: light dark;
            --accent: #2563eb;
            --accent-dark: #1d4ed8;
            --bg: #0f172a;
            --text: #0f172a;
            --text-muted: #475569;
            --card-bg: #ffffff;
            --border: #e2e8f0;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: 'Inter', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: var(--text);
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        }
        header {
            background: radial-gradient(circle at top left, rgba(37,99,235,0.14), transparent 55%), #0f172a;
            color: #f8fafc;
            text-align: center;
            padding: 80px 20px 96px;
        }
        header h1 {
            font-size: 2.9rem;
            margin: 0 0 16px;
            line-height: 1.1;
        }
        header p {
            max-width: 820px;
            margin: 0 auto 32px;
            font-size: 1.15rem;
            color: rgba(248, 250, 252, 0.9);
        }
        .eyebrow {
            text-transform: uppercase;
            letter-spacing: 0.18em;
            font-size: 0.78rem;
            margin-bottom: 18px;
            color: rgba(248, 250, 252, 0.75);
        }
        .cta-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 16px;
        }
        .cta-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 15px 28px;
            font-size: 1.05rem;
            font-weight: 600;
            border-radius: 999px;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, color 0.2s ease;
        }
        .cta-primary {
            color: #fff;
            background: var(--accent);
            box-shadow: 0 15px 35px rgba(37,99,235,0.35);
        }
        .cta-secondary {
            color: #fff;
            border: 1px solid rgba(248,250,252,0.4);
            background: rgba(15,23,42,0.35);
        }
        .cta-button:hover,
        .cta-button:focus {
            transform: translateY(-2px);
        }
        .cta-primary:hover,
        .cta-primary:focus {
            background: var(--accent-dark);
            box-shadow: 0 20px 45px rgba(29,78,216,0.4);
        }
        .cta-secondary:hover,
        .cta-secondary:focus {
            background: rgba(15,23,42,0.55);
        }
        main {
            max-width: 1140px;
            margin: -56px auto 0;
            padding: 0 20px 88px;
        }
        section {
            background: var(--card-bg);
            border-radius: 24px;
            padding: 40px;
            margin-bottom: 32px;
            box-shadow: 0 30px 60px rgba(15, 23, 42, 0.12);
            border: 1px solid rgba(148, 163, 184, 0.18);
        }
        h2 {
            margin-top: 0;
            font-size: 1.9rem;
            color: #0f172a;
        }
        h3 {
            margin-top: 28px;
            margin-bottom: 12px;
            font-size: 1.3rem;
            color: #1e293b;
        }
        p {
            color: var(--text-muted);
            line-height: 1.7;
        }
        ul, ol {
            color: var(--text-muted);
            line-height: 1.7;
            padding-left: 20px;
        }
        .pill-list {
            list-style: none;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            padding: 0;
            margin: 0;
        }
        .pill-list li {
            background: rgba(37, 99, 235, 0.08);
            color: #1d4ed8;
            padding: 10px 18px;
            border-radius: 999px;
            font-weight: 600;
            letter-spacing: 0.01em;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
        }
        .card {
            border: 1px solid rgba(148, 163, 184, 0.25);
            border-radius: 18px;
            padding: 24px;
            background: #ffffff;
            box-shadow: 0 12px 28px rgba(148,163,184,0.2);
        }
        .metadata-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 18px;
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .metadata-list li {
            padding: 16px;
            border: 1px solid rgba(148, 163, 184, 0.3);
            border-radius: 18px;
            background: rgba(37, 99, 235, 0.04);
        }
        blockquote {
            border-left: 4px solid var(--accent);
            padding-left: 16px;
            color: #1f2937;
            font-style: italic;
            margin: 0;
        }
        footer {
            text-align: center;
            padding: 32px 20px 48px;
            color: #64748b;
            font-size: 0.95rem;
        }
        @media (max-width: 640px) {
            header h1 { font-size: 2.2rem; }
            section { padding: 32px; }
        }
    </style>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "GitHub Plugin Installer & Updater",
        "operatingSystem": "WordPress",
        "applicationCategory": "DeveloperApplication",
        "softwareVersion": "2.0.0",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Websage Solutions",
            "url": "https://websage.solutions"
        },
        "author": {
            "@type": "Organization",
            "name": "Websage Solutions Lab",
            "url": "https://websage.solutions"
        },
        "description": "Install or refresh any WordPress plugin directly from a GitHub repository. Map repositories, authorize private downloads, and automate updates without leaving wp-admin.",
        "featureList": [
            "Install from GitHub repository URL",
            "Managed Plugins table",
            "Manual update dropdown",
            "Private repository token storage",
            "Self-update support",
            "WordPress multisite compatibility"
        ]
    }
    </script>
        <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-V5FKLV746D"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-V5FKLV746D');
    </script>
</head>
<body>
    <header>
        <?php if ($heroEyebrow): ?>
            <p class="eyebrow"><?= esc($heroEyebrow) ?></p>
        <?php endif; ?>
        <h1><?= esc($heroHeading) ?></h1>
        <p><?= esc($heroBody) ?></p>
        <div class="cta-row">
            <a class="cta-button cta-primary" href="<?= esc($downloadUrl) ?>"><?= esc($downloadLabel) ?></a>
            <a class="cta-button cta-secondary" href="<?= esc($githubUrl) ?>"><?= esc($githubLabel) ?></a>
        </div>
    </header>
    <main>
        <section id="plugin-metadata">
            <h2>Plugin metadata &amp; compatibility</h2>
            <p>Everything WordPress.org expects is packaged inside the accompanying <code>readme.txt</code>. Here are the highlights:</p>
            <ul class="metadata-list">
                <li><strong>Contributors:</strong> Websage Solutions Lab</li>
                <li><strong>Requires WordPress:</strong> 6.0 or higher</li>
                <li><strong>Tested up to:</strong> WordPress 6.5</li>
                <li><strong>Requires PHP:</strong> 7.4+</li>
                <li><strong>Stable tag:</strong> 2.0.0</li>
                <li><strong>License:</strong> GPLv2 or later</li>
            </ul>
        </section>
        <section id="why-release">
            <h2>Why release it?</h2>
            <p>Agencies and product teams repeatedly ship private plugins that never touch WordPress.org. Manual zip uploads slow the process, break automation, and make it hard for non-technical site managers to help. GitHub Plugin Installer and Updater replaces that manual work with an interface that understands GitHub releases.</p>
            <blockquote>“Install or refresh plugins directly from GitHub, keep every environment aligned, and document which repository powers each build.”</blockquote>
        </section>
        <section id="key-capabilities">
            <h2>Key capabilities</h2>
            <div class="grid">
                <div class="card">
                    <h3>Install from repositories</h3>
                    <p>Paste any GitHub repository URL, choose a branch or tag, and let the plugin handle the download, extraction, and activation.</p>
                </div>
                <div class="card">
                    <h3>Managed Plugins table</h3>
                    <p>Associate existing plugins with their GitHub projects so anyone can run updates from a single dashboard.</p>
                </div>
                <div class="card">
                    <h3>Manual updates on demand</h3>
                    <p>Trigger a refresh from wp-admin or directly from the Plugins list whenever a new release ships.</p>
                </div>
                <div class="card">
                    <h3>Private repository support</h3>
                    <p>Store a personal access token securely in WordPress options to authenticate downloads from private repos.</p>
                </div>
                <div class="card">
                    <h3>Self-update aware</h3>
                    <p>Point the helper plugin to its own repository and keep it current without manual zip uploads.</p>
                </div>
                <div class="card">
                    <h3>Multisite ready</h3>
                    <p>Network administrators can configure repositories once and let every site stay in sync.</p>
                </div>
            </div>
        </section>
        <section id="feature-list">
            <h2>Feature checklist</h2>
            <ul class="pill-list">
                <li>Install from GitHub repository URL</li>
                <li>Managed Plugins table</li>
                <li>Manual update dropdown</li>
                <li>Private token storage</li>
                <li>Self-update support</li>
                <li>WordPress multisite compatibility</li>
            </ul>
            <h3>Why teams choose Websage Solutions</h3>
            <p>Manual zip uploads slow down releases, especially when juggling client sites. Websage Solutions created this plugin so agencies and product teams can standardize deployments, cut the wait for WordPress.org approvals, give non-technical teams safe access to updates, and audit repositories powering each plugin at a glance.</p>
        </section>
        <section id="getting-started">
            <h2>Getting started</h2>
            <ol>
                <li>Download the latest release using the button above or grab the zip from the Websage Solutions Lab repository.</li>
                <li>In WordPress, navigate to <em>Plugins → Add New → Upload Plugin</em> and upload the downloaded zip.</li>
                <li>Activate the plugin and head to <em>Tools → Github Plugin Installer and Updater</em> to configure repositories and optional tokens.</li>
                <li>Use the Managed Plugins table to map existing installations and trigger updates whenever you ship a new release.</li>
            </ol>
        </section>
        <section id="faq">
            <h2>Frequently asked questions</h2>
            <h3>Does it work with private repositories?</h3>
            <p>Yes. Generate a GitHub personal access token with the <code>repo</code> scope and paste it into the settings page to authenticate downloads.</p>
            <h3>Can I manage multiple plugins?</h3>
            <p>Absolutely. Use the Managed Plugins table to map each installed plugin to its GitHub repository and branch or tag.</p>
            <h3>How do self-updates work?</h3>
            <p>Provide the helper plugin's own repository URL and it will notify you when a new release is available. You can trigger the update from the settings screen or directly from the Plugins list.</p>
        </section>
        <section id="release-notes">
            <h2>Release notes</h2>
            <h3>2.0.0 – Manage every plugin from GitHub</h3>
            <ul>
                <li>Add a Managed Plugins table to map multiple installed plugins to repositories.</li>
                <li>Introduce a dropdown-powered updater for selecting which plugin to refresh on demand.</li>
                <li>Provide manual self-update buttons on the settings screen and Plugins list.</li>
                <li>Refresh the plugin header to include WordPress compatibility metadata.</li>
            </ul>
            <h3>1.0.2 – Test self-update notification</h3>
            <ul>
                <li>Bump the plugin version again so WordPress surfaces the latest build when testing the self-update workflow.</li>
            </ul>
            <h3>1.0.1 – Test update</h3>
            <ul>
                <li>Expose the plugin version constant for enqueueing assets.</li>
                <li>Purge the GitHub response cache whenever WordPress refreshes plugin updates and reduce cache TTL to five minutes.</li>
            </ul>
            <h3>1.0.0 – Initial release</h3>
            <ul>
                <li>Initial public launch of GitHub Plugin Installer and Updater.</li>
            </ul>
        </section>
        <section id="contact">
            <h2>Need help?</h2>
            <p>Have a question about GitHub authentication, custom workflows, or managed deployments? Email <a href="mailto:<?= esc($contactEmail) ?>"><?= esc($contactEmail) ?></a> and the Websage Solutions Lab team will walk you through the setup.</p>
        </section>
    </main>
    <footer>
        <p>&copy; <span id="year"></span> Websage Solutions. All rights reserved.</p>
    </footer>
    <script>
        document.getElementById('year').textContent = new Date().getFullYear();
    </script>
</body>
</html>
