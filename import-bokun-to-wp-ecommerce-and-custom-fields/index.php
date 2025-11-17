<?php
declare(strict_types=1);

require __DIR__ . '/../lib/settings.php';

$config = get_plugin_page_settings('import-bokun-to-wp-ecommerce-and-custom-fields');
$siteSettings = $config['site'];
$pluginSettings = $config['plugin'];

function esc(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$siteTitle       = $pluginSettings['site_title'] ?? 'Import Bokun to WP Ecommerce and Custom Fields | Websage Solutions';
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
                padding: 64px 16px 80px;
            }
            header h1 {
                font-size: 2.2rem;
            }
        }
    </style>
</head>
<body>
<header>
    <div class="eyebrow"><?= esc($heroEyebrow) ?></div>
    <h1><?= esc($heroHeading) ?></h1>
    <p><?= esc($heroBody) ?></p>
    <div class="cta-row">
        <a class="cta-button cta-primary" href="<?= esc($downloadUrl) ?>" target="_blank" rel="noopener">
            <?= esc($downloadLabel) ?>
        </a>
        <a class="cta-button cta-secondary" href="<?= esc($githubUrl) ?>" target="_blank" rel="noopener">
            <?= esc($githubLabel) ?>
        </a>
    </div>
</header>
<main>
    <section>
        <h2>Why Bokun Bookings Management?</h2>
        <p>Import Bokun to WP Ecommerce and Custom Fields gives tour and activity teams a first-class booking workspace in\
side WordPress. Fetch reservations via the Bokun API, enrich them with taxonomies and custom fields, and expose shortcodes so\
 staff can trigger imports or review history without touching wp-admin.</p>
        <ul class="metadata-list">
            <li><strong>WordPress</strong><br>Version 6.0 or newer</li>
            <li><strong>PHP</strong><br>7.4+ with cURL enabled</li>
            <li><strong>Requires</strong><br>Bokun account with API key + secret</li>
            <li><strong>Custom Post Type</strong><br><code>bokun_booking</code> with taxonomies</li>
        </ul>
    </section>

    <section>
        <h2>Feature Highlights</h2>
        <div class="grid">
            <div class="card">
                <h3>Multiple API Credentials</h3>
                <p>Store, validate, and remove any number of Bokun API key and secret pairs. Each credential set becomes its own import context, making it easy to process bookings from multiple Bokun environments.</p>
            </div>
            <div class="card">
                <h3>Progress-Aware Imports</h3>
                <p>Secure admin actions paginate through the Booking Search endpoint, saving bookings while reporting completion stats, errors, and queued contexts so teams know exactly where the import stands.</p>
            </div>
            <div class="card">
                <h3>Dedicated Post Types</h3>
                <p>Bookings are stored as the <code>bokun_booking</code> post type with Booking Status, Product Tags, and Team Member taxonomies so you can filter data in WP Ecommerce, Elementor, or custom REST endpoints.</p>
            </div>
            <div class="card">
                <h3>Dashboards &amp; Shortcodes</h3>
                <p>Drop <code>[bokun_booking_dashboard]</code>, <code>[bokun_booking_history]</code>, or <code>[bokun_fetch_button]</code> onto any page. The plugin can even append the dashboard automatically to a selected page.</p>
            </div>
            <div class="card">
                <h3>Rich Booking History</h3>
                <p>An accessible DataTable powers booking history views and CSV exports. Column filters, capability checks, and status grouping help admins audit every change.</p>
            </div>
            <div class="card">
                <h3>Media Utilities</h3>
                <p>Trigger background tasks that import gallery images for each Product Tag taxonomy term so your customer-facing UI stays on brand.</p>
            </div>
            <div class="card">
                <h3>Accessible Progress UI</h3>
                <p>ARIA-enabled progress bars and polite live regions inform staffers about import states whether they use the admin panel or the public fetch button.</p>
            </div>
        </div>
    </section>

    <section>
        <h2>Installation</h2>
        <ol>
            <li>Download the plugin ZIP using the button above.</li>
            <li>Upload or clone the <code>bokun-bookings-management</code> folder to <code>wp-content/plugins/</code>.</li>
            <li>Ensure the directory name matches the plugin slug so WordPress can read the header in <code>bokun-bookings-management.php</code>.</li>
            <li>Activate the plugin. On first run it creates the <code>wp_bokun_booking_history</code> table and redirects you to the settings screen.</li>
        </ol>
    </section>

    <section>
        <h2>Configure Credentials &amp; Dashboards</h2>
        <ol>
            <li>Navigate to <strong>Bokun Bookings Management → Settings</strong>.</li>
            <li>Add one or more API keys and secrets. Use the “Add another API” control to define multiple contexts.</li>
            <li>Select the page that should automatically display the booking dashboard or insert the shortcode manually.</li>
            <li>Use the Fetch Booking panel to test the import pipeline and watch real-time progress output.</li>
            <li>Run the optional Product Tag image importer when you need taxonomy media synced from Bokun.</li>
        </ol>
    </section>

    <section>
        <h2>Import Behavior</h2>
        <ul>
            <li>Every credential set becomes a sequential import context (API 1, API 2, etc.).</li>
            <li>The plugin calls <code>/booking.json/booking-search</code> with a default window from yesterday through one month ahead. Filter <code>bokun_booking_items_per_page</code> to change pagination.</li>
            <li>Bookings are stored as the <code>bokun_booking</code> post type and forced to <code>publish</code> so future-dated reservations show up immediately.</li>
            <li>Each create/update action is persisted to <code>wp_bokun_booking_history</code> with actor, source, and checked state metadata.</li>
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
                    <td>Renders a front-end button that triggers the importer plus the shared progress UI.</td>
                    <td>None.</td>
                </tr>
                <tr>
                    <td><code>[bokun_booking_history]</code></td>
                    <td>Outputs the booking history DataTable anywhere.</td>
                    <td><code>limit</code> (default 100), <code>capability</code> (default <code>manage_options</code>), <code>export</code> (CSV filename slug).</td>
                </tr>
                <tr>
                    <td><code>[bokun_booking_dashboard]</code></td>
                    <td>Displays the dashboard cards, filters, and charts inside your selected page.</td>
                    <td>None.</td>
                </tr>
            </tbody>
        </table>
    </section>

    <section>
        <h2>Admin Booking History</h2>
        <p>The plugin ships with a Booking History submenu that displays the most recent entries stored in <code>wp_bokun_booking_history</code>. Users can filter by action, status, actor, and source, plus download the visible dataset as CSV through DataTables Buttons and JSZip. The view gracefully handles missing tables (e.g., before activation) and respects capability checks.</p>
    </section>

    <section>
        <h2>Hooks &amp; Filters</h2>
        <ul>
            <li><code>bokun_booking_items_per_page</code> – Change how many bookings are pulled per API page (default 50).</li>
            <li><code>bokun_booking_request_timeout</code> – Adjust the cURL timeout (default 300 seconds).</li>
            <li><code>bokun_booking_history_page_limit</code> – Set how many rows display on the admin booking history page (default 100).</li>
            <li><code>bokun_txt_domain</code> – Fired after the text domain loads so you can register additional strings.</li>
        </ul>
    </section>

    <section>
        <h2>Development Workflow</h2>
        <ol>
            <li>Install the plugin in a local WordPress environment.</li>
            <li>Run <code>npm install</code> and <code>npm run dev</code> inside <code>assets/</code> if you extend the JavaScript/CSS helpers (current build is plain CSS/JS).</li>
            <li>Use <code>wp i18n make-pot</code> to refresh translation files whenever you change user-facing strings.</li>
            <li>Follow WordPress PHP coding standards and escape all output.</li>
        </ol>
    </section>

    <section>
        <h2>Troubleshooting</h2>
        <ul>
            <li><strong>Error: No API credentials available</strong> – Ensure at least one credential pair is saved; legacy single-key installs are migrated the next time you view the settings screen.</li>
            <li><strong>Booking history table missing</strong> – Reactivate the plugin to trigger <code>dbDelta</code> and recreate <code>wp_bokun_booking_history</code>.</li>
            <li><strong>Imports timing out</strong> – Reduce the booking window or add filters to shrink payloads, and confirm the server can reach <code>api.bokun.io</code>.</li>
        </ul>
    </section>

    <section>
        <h2>Need Help?</h2>
        <p>Email <a href="mailto:<?= esc($contactEmail) ?>"><?= esc($contactEmail) ?></a> with deployment questions, feature requests, or support needs.</p>
    </section>
</main>
</body>
</html>
