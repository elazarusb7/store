<?php

/**
 * @file
 */

/**
 * Load services definition file.
 */
if (file_exists($app_root . '/' . $site_path . '/services.local.yml')) {
  $settings['container_yamls'][] = $app_root . '/' . $site_path . '/services.local.yml';
}

// It is very important that you set the right config split option here.
// For official testing and production, use 'prod'. For development, use 'dev'.
$config['config_split.config_split.prod']['status'] = FALSE;
$config['config_split.config_split.dev']['status'] = TRUE;

$settings['config_sync_directory'] = '../config/sync';
$settings['hash_salt'] = '9d5d5d56a356305c4caeea9e06ba811720e74a14286918e83aea7e8cdda19ec3';

$databases['default']['default'] = [
  'database' => 'tugboat',
  'username' => 'tugboat',
  'password' => 'tugboat',
  'prefix' => '',
  'host' => 'mysql',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
];
