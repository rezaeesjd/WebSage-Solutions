<?php
declare(strict_types=1);

/**
 * Helpers for reading and writing the site configuration that powers
 * the marketing page and dashboard UI.
 */

const SITE_SETTINGS_PATH = __DIR__ . '/../config/site-settings.json';

/**
 * Default settings used when a value has not been saved yet.
 */
function site_settings_defaults(): array
{
    return [
        'site_title'        => 'GitHub Plugin Installer & Updater | Websage Solutions',
        'meta_description'  => 'Install or refresh any WordPress plugin straight from GitHub without leaving wp-admin.',
        'hero_eyebrow'      => 'Publisher: Websage Solutions Lab • Company: Websage Solutions',
        'hero_heading'      => 'Github Plugin Installer & Updater for WordPress',
        'hero_body'         => 'Install or refresh any WordPress plugin straight from GitHub without leaving wp-admin. Map installed plugins to their repositories, authorize private downloads with a personal access token, and keep this helper plugin updated from the same dashboard.',
        'download_label'    => 'Download Plugin',
        'github_label'      => 'View on GitHub',
        'cta_download_url'  => 'https://github.com/rezaeesjd/github-updater/archive/refs/heads/main.zip',
        'cta_github_url'    => 'https://github.com/rezaeesjd/github-updater',
        'canonical_url'     => 'https://websage.solutions/github-plugin-installer-and-updater/',
        'og_image'          => 'https://websage.solutions/assets/github-updater-og.png',
        'contact_email'     => 'lab@websagesolutions.com',
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

    return array_merge($defaults, array_intersect_key($data, $defaults));
}

/**
 * Persist settings to disk.
 */
function save_site_settings(array $incoming): void
{
    $defaults = site_settings_defaults();
    $settings = $defaults;

    foreach ($defaults as $key => $defaultValue) {
        if (!array_key_exists($key, $incoming)) {
            continue;
        }
        $value = $incoming[$key];
        if (is_string($defaultValue)) {
            $settings[$key] = trim((string)$value);
        } else {
            $settings[$key] = $value;
        }
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
