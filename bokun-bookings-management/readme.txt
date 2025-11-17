=== Bokun Bookings Management ===
Contributors: websagesolutionslab
Tags: bokun, bookings, importer, shortcode, dashboard, datatable
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Bokun Bookings Management lets tour and activity operators pull reservations from the Bokun API, persist them as the `bokun_booking` custom post type, and work with the data directly inside WordPress. The plugin exposes booking dashboards, a booking-history DataTable with CSV export, public-facing shortcodes, and utility actions for managing product-tag media.

== Features ==
* Save any number of Bokun API key/secret pairs from a repeatable UI that writes to the `bokun_api_credentials` option.
* Trigger booking imports from secure admin/AJAX actions that paginate through the Bokun Booking Search endpoint while reporting progress and errors.
* Store bookings as a dedicated post type enriched with Booking Status, Product Tag, and Team Member taxonomies.
* Embed booking dashboards, booking history tables, or the fetch button anywhere using shortcodes.
* Review every import via a responsive, filterable DataTable that supports column search and CSV export.
* Pull gallery images for each Bokun Product Tag and attach them to WordPress taxonomy terms.
* Provide ARIA-friendly progress bars and live regions so screen readers can follow import status.

== Requirements ==
* WordPress 6.0 or newer.
* PHP 7.4+ with cURL enabled so the importer can reach the Bokun API.
* A Bokun account with API access plus at least one API key/secret pair.

== Installation ==
1. Download the latest release zip or clone the repository into `wp-content/plugins/`.
2. Ensure the folder is named `bokun-bookings-management` so WordPress can detect the plugin header.
3. Activate the plugin. On first activation it creates the `wp_bokun_booking_history` table and redirects to the settings page.

== Configuring API credentials ==
1. Go to **Bokun Bookings Management → Settings**.
2. Add one or more API key/secret pairs using the “Add another API” button and save the form.
3. (Optional) Choose a page that should automatically display the booking dashboard, or place the `[bokun_booking_dashboard]` shortcode manually.
4. Use the Fetch Booking panel to start an import and watch the inline progress UI.
5. Run the Import Product Tag Images action whenever you want taxonomy terms to inherit Bokun gallery media.

== Shortcodes ==
`[bokun_fetch_button]` — Renders a front-end fetch button with progress bar.

`[bokun_booking_history]` — Outputs the booking history DataTable. Attributes: `limit` (default 100), `capability` (default `manage_options`), `export` (slug used for the CSV filename).

`[bokun_booking_dashboard]` — Displays the dashboard cards and filters anywhere.

== Hooks & Filters ==
* `bokun_booking_items_per_page` — Change the number of bookings requested per page (default 50).
* `bokun_booking_request_timeout` — Adjust the cURL timeout in seconds (default 300).
* `bokun_booking_history_page_limit` — Control how many rows display in the admin booking history view (default 100).
* `bokun_txt_domain` — Fires after the text domain loads so you can register additional translations.

== Troubleshooting ==
* **Error: No API credentials available for this import.** — Save at least one credential pair; legacy single-key installs are migrated the next time the settings screen loads.
* **Booking history table missing.** — Reactivate the plugin to trigger `dbDelta` and recreate the `wp_bokun_booking_history` table.
* **Imports time out.** — Narrow the booking window or filter `bokun_booking_items_per_page` to reduce payload size, and confirm the server can reach `api.bokun.io`.
