<?php
declare(strict_types=1);

/**
 * Helpers for reading and writing the site configuration that powers
 * the marketing page and dashboard UI.
 */

const SITE_SETTINGS_PATH = __DIR__ . '/../config/site-settings.json';

/**
 * Default structure for plugin entries.
 */
function base_plugin_defaults(): array
{
    return [
        'slug'             => '',
        'card_title'       => '',
        'card_excerpt'     => '',
        'site_title'       => '',
        'meta_description' => '',
        'hero_eyebrow'     => '',
        'hero_heading'     => '',
        'hero_body'        => '',
        'download_label'   => 'Download Plugin',
        'github_label'     => 'View on GitHub',
        'cta_download_url' => '',
        'cta_github_url'   => '',
        'canonical_url'    => '',
        'og_image'         => '',
    ];
}

/**
 * Default settings used when a value has not been saved yet.
 */
function site_settings_defaults(): array
{
    return [
        'site' => [
            'site_title'       => 'Websage Solutions Plugins | Websage Solutions',
            'meta_description' => 'Explore Websage Solutions\' lightweight WordPress utilities backed by GitHub-powered deployments and Bokun booking imports.',
            'hero_eyebrow'     => 'Websage Solutions Lab',
            'hero_heading'     => 'Purpose-built WordPress helpers',
            'hero_body'        => 'Pick a plugin landing page below to explore documentation, download links, and GitHub repositories.',
            'contact_email'    => 'lab@websagesolutions.com',
        ],
        'plugins' => [
            'github-plugin-installer-and-updater' => array_merge(base_plugin_defaults(), [
                'slug'              => 'github-plugin-installer-and-updater',
                'card_title'        => 'GitHub Plugin Installer & Updater',
                'card_excerpt'      => 'Install and refresh WordPress plugins directly from GitHub, map installed plugins to repositories, and authorize private downloads with a token.',
                'site_title'        => 'GitHub Plugin Installer & Updater | Websage Solutions',
                'meta_description'  => 'Install or refresh any WordPress plugin straight from GitHub without leaving wp-admin.',
                'hero_eyebrow'      => 'Publisher: Websage Solutions Lab • Company: Websage Solutions',
                'hero_heading'      => 'Github Plugin Installer & Updater for WordPress',
                'hero_body'         => 'Install or refresh any WordPress plugin straight from GitHub without leaving wp-admin. Map installed plugins to their repositories, authorize private downloads with a personal access token, and keep this helper plugin updated from the same dashboard.',
                'cta_download_url'  => 'https://github.com/rezaeesjd/github-updater/archive/refs/heads/main.zip',
                'cta_github_url'    => 'https://github.com/rezaeesjd/github-updater',
                'canonical_url'     => 'https://websage.solutions/github-plugin-installer-and-updater/',
                'og_image'          => 'https://websage.solutions/assets/github-updater-og.png',
            ]),
            'import-bokun-to-wp-ecommerce-and-custom-fields' => array_merge(base_plugin_defaults(), [
                'slug'              => 'import-bokun-to-wp-ecommerce-and-custom-fields',
                'card_title'        => 'Import Bokun to WP Ecommerce and Custom Fields',
                'card_excerpt'      => 'Pull Bokun reservations into WordPress as first-class posts, power dashboards, and expose booking history tables with CSV export.',
                'site_title'        => 'Import Bokun to WP Ecommerce and Custom Fields | Websage Solutions',
                'meta_description'  => 'Pull reservations from the Bokun API, store them as native WordPress posts, and work with booking data inside WP Ecommerce with custom fields, dashboards, and shortcodes.',
                'hero_eyebrow'      => 'WordPress Plugin • Built by Websage Solutions Lab',
                'hero_heading'      => 'Import Bokun to WP Ecommerce and Custom Fields',
                'hero_body'         => 'Bokun Bookings Management lets tour and activity operators pull reservations straight from the Bokun API, save them as custom posts, and expose dashboards, DataTables history, and shortcodes that plug into WP Ecommerce workflows.',
                'cta_download_url'  => 'https://github.com/rezaeesjd/bokun-bookings-management/archive/refs/heads/main.zip',
                'cta_github_url'    => 'https://github.com/rezaeesjd/bokun-bookings-management',
                'canonical_url'     => 'https://websage.solutions/import-bokun-to-wp-ecommerce-and-custom-fields/',
                'og_image'          => 'https://websage.solutions/assets/bokun-bookings-management-og.png',
            ]),
        ],
    ];
}

function merge_settings_block(array $defaults, array $incoming): array
{
    $merged = $defaults;
    foreach ($defaults as $key => $defaultValue) {
        if (!array_key_exists($key, $incoming)) {
            continue;
        }
        $value = $incoming[$key];
        if (is_string($defaultValue)) {
            $merged[$key] = trim((string)$value);
        } else {
            $merged[$key] = $value;
        }
    }

    return $merged;
}

function sanitize_slug(string $value): string
{
    $slug = strtolower(trim($value));
    $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
    return trim((string)$slug, '-');
}

function get_plugin_page_settings(string $slug): array
{
    $slugKey = sanitize_slug($slug);
    $settings = load_site_settings();
    $defaults = site_settings_defaults();
    $pluginDefaults = $defaults['plugins'][$slugKey] ?? array_merge(base_plugin_defaults(), ['slug' => $slugKey]);
    $plugin = $settings['plugins'][$slugKey] ?? $pluginDefaults;

    return [
        'site'   => merge_settings_block($defaults['site'], $settings['site'] ?? []),
        'plugin' => $plugin,
    ];
}

/**
 * Load settings merged with defaults.
 */
function load_site_settings(): array
{
    $defaults = site_settings_defaults();
    if (!file_exists(SITE_SETTINGS_PATH)) {
        save_site_settings($defaults);
        return $defaults;
    }

    $json = file_get_contents(SITE_SETTINGS_PATH);
    $data = json_decode((string)$json, true);
    if (!is_array($data)) {
        return $defaults;
    }

    $settings = $defaults;

    if (isset($data['site']) && is_array($data['site'])) {
        $settings['site'] = merge_settings_block($defaults['site'], $data['site']);
    }

    $settings['plugins'] = [];
    if (isset($data['plugins']) && is_array($data['plugins'])) {
        foreach ($data['plugins'] as $slug => $pluginData) {
            if (!is_array($pluginData)) {
                continue;
            }
            $slugKey = sanitize_slug(is_string($slug) ? $slug : ($pluginData['slug'] ?? ''));
            if ($slugKey === '') {
                continue;
            }
            $pluginDefaults = $defaults['plugins'][$slugKey] ?? array_merge(base_plugin_defaults(), ['slug' => $slugKey]);
            $pluginData['slug'] = $slugKey;
            $settings['plugins'][$slugKey] = merge_settings_block($pluginDefaults, $pluginData);
        }
    }

    foreach ($defaults['plugins'] as $slug => $pluginDefaults) {
        if (!isset($settings['plugins'][$slug])) {
            $settings['plugins'][$slug] = $pluginDefaults;
        }
    }

    return $settings;
}

/**
 * Persist settings to disk.
 */
function save_site_settings(array $incoming): void
{
    $defaults = site_settings_defaults();
    $settings = $defaults;

    if (isset($incoming['site']) && is_array($incoming['site'])) {
        $settings['site'] = merge_settings_block($defaults['site'], $incoming['site']);
    }

    $settings['plugins'] = [];
    if (isset($incoming['plugins']) && is_array($incoming['plugins'])) {
        foreach ($incoming['plugins'] as $slug => $pluginData) {
            if (!is_array($pluginData)) {
                continue;
            }
            $slugKey = sanitize_slug(is_string($slug) ? $slug : ($pluginData['slug'] ?? ''));
            if ($slugKey === '') {
                continue;
            }
            $pluginDefaults = $defaults['plugins'][$slugKey] ?? array_merge(base_plugin_defaults(), ['slug' => $slugKey]);
            $pluginData['slug'] = $slugKey;
            $settings['plugins'][$slugKey] = merge_settings_block($pluginDefaults, $pluginData);
        }
    }

    if (empty($settings['plugins'])) {
        $settings['plugins'] = $defaults['plugins'];
    }

    $dir = dirname(SITE_SETTINGS_PATH);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    file_put_contents(
        SITE_SETTINGS_PATH,
        json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
    );
}
