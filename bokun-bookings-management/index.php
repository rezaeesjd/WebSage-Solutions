<?php
declare(strict_types=1);

require __DIR__ . '/../lib/settings.php';

$config = get_plugin_page_settings('bokun-bookings-management');
$siteSettings = $config['site'];
$pluginSettings = $config['plugin'];

function esc(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$siteTitle       = $pluginSettings['site_title'] ?? 'Bokun Bookings Management | Websage Solutions';
$metaDescription = $pluginSettings['meta_description'] ?? '';
$heroEyebrow     = $pluginSettings['hero_eyebrow'] ?? '';
$heroHeading     = $pluginSettings['hero_heading'] ?? '';
$heroBody        = $pluginSettings['hero_body'] ?? '';
$downloadLabel   = $pluginSettings['download_label'] ?? 'Download Plugin';
$githubLabel     = $pluginSettings['github_label'] ?? 'View on GitHub';
$downloadUrl     = $pluginSettings['cta_download_url'] ?? '#';
$githubUrl       = $pluginSettings['cta_github_url'] ?? '#';
$canonicalUrl    = $pluginSettings['canonical_url'] ?? '';
$ogImage         = $pluginSettings['og_image'] ?? '';
$contactEmail    = $siteSettings['contact_email'] ?? 'lab@websagesolutions.com';
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
        <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-V5FKLV746D"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-V5FKLV746D');
    </script>
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
            position: relative;
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
        .lab-logo {
            position: absolute;
            top: 32px;
            left: 32px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            background: rgba(15, 23, 42, 0.45);
            border: 1px solid rgba(248, 250, 252, 0.25);
            color: #f8fafc;
            backdrop-filter: blur(8px);
        }
        .lab-logo span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
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
            margin: 0 auto;
            padding: 56px 20px 88px;
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
        .metadata-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
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
        pre {
            background: #0f172a;
            color: #f8fafc;
            border-radius: 16px;
            padding: 24px;
            overflow-x: auto;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        table th, table td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.3);
        }
        table th {
            color: #0f172a;
        }
        @media (max-width: 640px) {
            section {
                padding: 28px;
            }
            header {
                padding: 72px 16px 80px;
            }
            header h1 {
                font-size: 2.2rem;
            }
            .lab-logo {
                position: static;
                margin-bottom: 18px;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
<header>
    <a class="lab-logo" href="/" aria-label="Back to the Websage Solutions homepage">
        <span><span class="lab-logo-dot" aria-hidden="true"></span>Websage Solutions Lab</span>
    </a>
    <div class="eyebrow"><?= esc($heroEyebrow) ?></div>
    <h1><?= esc($heroHeading) ?></h1>
    <p><?= esc($heroBody) ?></p>
    <div class="cta-row">
        <a
            class="cta-button cta-primary"
            href="<?= esc($downloadUrl) ?>"
            target="_blank"
            rel="noopener"
            data-download-cta
            data-plugin-slug="<?= esc($pluginSettings['slug'] ?? 'bokun-bookings-management') ?>"
        >
            <?= esc($downloadLabel) ?>
        </a>
        <a class="cta-button cta-secondary" href="<?= esc($githubUrl) ?>" target="_blank" rel="noopener">
            <?= esc($githubLabel) ?>
        </a>
    </div>
</header>
<main>
    <section>
        <h2>Purpose-built Bokun booking imports for WordPress</h2>
        <p>Bokun Bookings Management helps tour and activity operators sync Bokun reservations into WordPress, store them as the
            <code>bokun_booking</code> custom post type, and activate the data in dashboards, Elementor widgets, or custom workflows.
            Multiple API credentials, ARIA-friendly progress bars, and front-end shortcodes mean staff can fetch bookings without
            touching wp-admin.</p>
        <ul class="metadata-list">
            <li><strong>WordPress</strong><br>6.0 or newer</li>
            <li><strong>PHP</strong><br>7.4+ with cURL enabled</li>
            <li><strong>API access</strong><br>Bokun account with key &amp; secret</li>
            <li><strong>Custom post type</strong><br><code>bokun_booking</code> + taxonomies</li>
        </ul>
    </section>

    <section>
        <h2>Repository layout</h2>
        <p>Everything ships as a standard WordPress plugin that lives under <code>wp-content/plugins/bokun-bookings-management</code>.</p>
        <pre>
├── bokun-bookings-management.php
├── includes/
│   ├── bokun-bookings-manager.php
│   ├── bokun_settings.class.php
│   ├── bokun_shortcode.class.php
│   ├── bokun_settings.view.php
│   └── bokun_booking_history.view.php
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
└── addons/
        </pre>
    </section>

    <section>
        <h2>Feature highlights for Bokun booking automation</h2>
        <div class="grid">
            <div class="card">
                <h3>Multiple credentials</h3>
                <p>Store, validate, and remove any number of Bokun API key/secret pairs. Each set becomes its own import context so you can process bookings from multiple Bokun tenants without editing code.</p>
            </div>
            <div class="card">
                <h3>Progress-aware imports</h3>
                <p>Admin/AJAX actions (such as <code>bokun_bookings_manager_page</code>) paginate through the Booking Search endpoint, write bookings to WordPress, and report progress, queued contexts, and errors back to the UI.</p>
            </div>
            <div class="card">
                <h3>First-class post type</h3>
                <p>Reservations are normalized into the <code>bokun_booking</code> post type with Booking Status, Product Tag, and Team Member taxonomies that power filtered dashboards, Elementor widgets, or REST/GraphQL queries.</p>
            </div>
            <div class="card">
                <h3>Dashboards &amp; shortcodes</h3>
                <p>Shortcodes like <code>[bokun_booking_dashboard]</code>, <code>[bokun_booking_history]</code>, and <code>[bokun_fetch_button]</code> bring dashboards, DataTables, and fetch buttons to any page—no admin access required.</p>
            </div>
            <div class="card">
                <h3>Rich booking history</h3>
                <p>The admin history view and history shortcode share a responsive DataTable with filters, CSV export (via DataTables Buttons/JSZip), and capability checks to prevent unauthorized viewing.</p>
            </div>
            <div class="card">
                <h3>Product tag media jobs</h3>
                <p>Trigger a background importer that pulls gallery images for every Bokun Product Tag and assigns them to WordPress taxonomy terms for polished, on-brand UI.</p>
            </div>
            <div class="card">
                <h3>Accessible status updates</h3>
                <p>Both the admin fetch button and the public <code>[bokun_fetch_button]</code> shortcode share ARIA-enabled progress bars and polite live regions so screen-reader users stay informed.</p>
            </div>
        </div>
    </section>

    <section>
        <h2>Common Bokun WordPress use cases</h2>
        <ul>
            <li><strong>Operations dashboards:</strong> give staff a shared view of upcoming tours without WordPress admin access.</li>
            <li><strong>Multi-brand imports:</strong> manage multiple Bokun tenants with separate API credentials and dashboards.</li>
            <li><strong>Customer service teams:</strong> search booking history, export CSVs, and track changes from one table.</li>
        </ul>
    </section>

    <section>
        <h2>Installation</h2>
        <ol>
            <li>Download the latest release zip via the button above or clone the repository into <code>wp-content/plugins/</code>.</li>
            <li>Make sure the directory is named <code>bokun-bookings-management</code> so WordPress can detect the plugin header.</li>
            <li>Activate the plugin. The first activation creates the <code>wp_bokun_booking_history</code> table and redirects to the settings screen.</li>
        </ol>
    </section>

    <section>
        <h2>Configure API credentials &amp; dashboard output</h2>
        <ol>
            <li>Visit <strong>Bokun Bookings Management → Settings</strong>.</li>
            <li>Add one or more API key/secret pairs using the repeatable “Add another API” interface—each set becomes a named context.</li>
            <li>Select a page that should automatically append the booking dashboard or drop the <code>[bokun_booking_dashboard]</code> shortcode anywhere.</li>
            <li>Use the Fetch Booking panel to start the importer and watch real-time progress without refreshing.</li>
            <li>Optional: run the Product Tag image importer to sync taxonomy media from Bokun.</li>
        </ol>
    </section>

    <section>
        <h2>Import behavior and data handling</h2>
        <ul>
            <li>Every credential pair is normalized into a numbered import context (API 1, API 2, etc.) that runs sequentially.</li>
            <li>The plugin calls <code>/booking.json/booking-search</code> with a default date window from yesterday through one month ahead—filter <code>bokun_booking_items_per_page</code> to adjust pagination.</li>
            <li>Bookings are stored as <code>bokun_booking</code> posts and forced to <code>publish</code> status so future-dated reservations appear immediately.</li>
            <li>Each create/update action logs to <code>wp_bokun_booking_history</code> with actor, status, and “checked” metadata for auditing.</li>
        </ul>
    </section>

    <section>
        <h2>Shortcodes</h2>
        <table>
            <thead>
            <tr>
                <th>Shortcode</th>
                <th>Purpose</th>
                <th>Attributes</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><code>[bokun_fetch_button]</code></td>
                <td>Displays a primary button that triggers the AJAX importer plus the shared progress UI.</td>
                <td>None.</td>
            </tr>
            <tr>
                <td><code>[bokun_booking_history]</code></td>
                <td>Outputs the responsive, filterable booking-history DataTable anywhere.</td>
                <td><code>limit</code> (default 100), <code>capability</code> (default <code>manage_options</code>), <code>export</code> (CSV filename slug).</td>
            </tr>
            <tr>
                <td><code>[bokun_booking_dashboard]</code></td>
                <td>Renders the dashboard cards, filters, and KPIs for team members and partners.</td>
                <td>None.</td>
            </tr>
            </tbody>
        </table>
    </section>

    <section>
        <h2>Booking history UI for teams</h2>
        <p>The built-in Booking History submenu and shortcode both query <code>wp_bokun_booking_history</code> and render a responsive DataTable with collapsible filters, column search, CSV export, and capability checks. The view handles missing tables gracefully—for example when the plugin has been uploaded but not activated yet.</p>
    </section>

    <section>
        <h2>Hooks &amp; filters</h2>
        <ul>
            <li><code>bokun_booking_items_per_page</code> – Change how many bookings the importer requests per API page (default 50).</li>
            <li><code>bokun_booking_request_timeout</code> – Adjust the cURL timeout in seconds (default 300).</li>
            <li><code>bokun_booking_history_page_limit</code> – Control how many rows the admin history screen displays (default 100).</li>
            <li><code>bokun_txt_domain</code> – Fires after the text domain loads so you can register additional translations.</li>
        </ul>
    </section>

    <section>
        <h2>Development workflow</h2>
        <ol>
            <li>Install the plugin in a local WordPress environment for testing.</li>
            <li>Run <code>npm install</code> and <code>npm run dev</code> inside <code>assets/</code> if you extend the CSS/JS helpers (current build is plain CSS/JS).</li>
            <li>Use <code>wp i18n make-pot</code> to refresh translation catalogs whenever you edit user-facing strings.</li>
            <li>Follow WordPress coding standards, escape output, and lint PHP with tools such as <code>wp coding standards</code>.</li>
        </ol>
    </section>

    <section>
        <h2>Troubleshooting</h2>
        <ul>
            <li><strong>Error: No API credentials available for this import.</strong> – Save at least one credential pair; legacy single-key installs are migrated the next time you load the settings screen.</li>
            <li><strong>Booking history table missing.</strong> – Reactivate the plugin to run <code>dbDelta</code> and recreate <code>wp_bokun_booking_history</code>.</li>
            <li><strong>Imports timing out.</strong> – Narrow the booking window, reduce <code>bokun_booking_items_per_page</code>, and confirm your host allows outbound HTTPS calls to <code>api.bokun.io</code>.</li>
        </ul>
    </section>

    <section>
        <h2>Need help?</h2>
        <p>Email <a href="mailto:<?= esc($contactEmail) ?>"><?= esc($contactEmail) ?></a> with deployment questions, feature requests, or support needs.</p>
    </section>
</main>
<script>
    (function () {
        var downloadLink = document.querySelector('[data-download-cta]');
        if (!downloadLink) {
            return;
        }

        function trackDownloadClick() {
            if (typeof gtag !== 'function') {
                return;
            }

            gtag('event', 'plugin_download_click', {
                plugin_slug: downloadLink.getAttribute('data-plugin-slug') || '',
                download_url: downloadLink.href,
                event_category: 'engagement',
                event_label: 'plugin_download',
                value: 1,
            });
        }

        downloadLink.addEventListener('click', trackDownloadClick);
    })();
</script>
</body>
</html>
