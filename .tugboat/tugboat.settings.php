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
// Solr configuration

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

$config['search_api.server.common'] = [
  'name' => 'Store Common (dev/local/tugboat overridden)',
  'backend_config' => [
    'connector' => 'basic_auth',
    'connector_config' => [
      'scheme' => 'http',
      'username' => 'ocwebteam',
      'password' => 'ramap',
      'host' => 'solr.ocweb-team.com',
      'path' => '/',
      'core' => 'common',
      'port' => '80',
    ],
  ],
];

$config['search_api.server.default_solr_server'] = [
  'name' => 'Store Solr Server (dev/local/tugboat overridden)',
  'backend_config' => [
    'connector' => 'basic_auth',
    'connector_config' => [
      'scheme' => 'http',
      'username' => 'ocwebteam',
      'password' => 'ramap',
      'host' => 'solr.ocweb-team.com',
      'path' => '/',
      'core' => 'drupal',
      'port' => '80',
    ],
  ],
];

// Adding a private folder
$settings['file_private_path'] = '../private';