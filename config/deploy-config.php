<?php
/**
 * Central deploy configuration shared between deploy.php and internal tooling.
 */

return [
    'secret_token' => 'deploytest_7uF9xK2aT6pR4qL1mZ8vW3nB0sC5eH9jP7rF6tY2uI4oN8wE3aD5gQ1lV7',
    'default_owner' => 'rezaeesjd',
    'default_repo'  => 'bokun-bookings-management',
    'default_ref'   => 'main',
    'alt_repos'     => [
        'github-updater' => ['owner' => 'rezaeesjd', 'repo' => 'github-updater', 'ref' => 'main'],
    ],
    'site_root'     => dirname(__DIR__),
    'keep_backup'   => true,
    'backup_dir'    => 'backups',
    'prune_removed' => true,
    'timeout_sec'   => 300,
    'excludes'      => [
        'deploy.php',
        '.git',
        '.github',
        'backups',
    ],
    'github_token'  => '',
];
