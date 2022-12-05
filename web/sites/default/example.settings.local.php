<?php
/**
 * @file
 * Sample settings.local.php file
 *
 * Copy this to settings.local.php and make needed changes there. settings.local.php
 * is ignored by git and will not be overwritten.
 */

// Load services definition file.
if (file_exists($app_root . '/' . $site_path . '/services.local.yml')) {
  $settings['container_yamls'][] = $app_root . '/' . $site_path . '/services.local.yml';
}

// It is very important that you set the right config split option here.
// For official testing and production, use 'prod'. For development, use 'dev'
// NOTE: If you change these values after having done a config import, you will
// need to clear cache for the changes to take effect.
// You can reverse these values to install production config instead of dev.
$config['config_split.config_split.prod']['status'] = FALSE;
$config['config_split.config_split.dev']['status'] = TRUE;

// Stop Drupal from breaking composer and such.
$settings['skip_permissions_hardening'] = TRUE;

// Everything below should be customized, removed, added to... Depending
// on your needs.
$databases = [];

// Solr configuration
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



$settings["config_sync_directory"] = '../config/sync';

// Setting this properly will fix a warning on the status report, but it may
// also cause problems with lando
// $settings['trusted_host_patterns'] = [
//   '^samhsa\.lndo\.site$',
// ];

// SAMHSA: To disable specific caches, uncomment these lines
// $settings['cache']['bins']['bootstrap'] = 'cache.backend.null';
// $settings['cache']['bins']['config'] = 'cache.backend.null';
// $settings['cache']['bins']['data'] = 'cache.backend.null';
// $settings['cache']['bins']['data'] = 'cache.backend.null';
// $settings['cache']['bins']['default'] = 'cache.backend.null';
// $settings['cache']['bins']['discovery'] = 'cache.backend.null';
// $settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
// $settings['cache']['bins']['forms'] = 'cache.backend.null';
// $settings['cache']['bins']['menu'] = 'cache.backend.null';
// $settings['cache']['bins']['page'] = 'cache.backend.null';
// $settings['cache']['bins']['render'] = 'cache.backend.null';
// $settings['cache']['bins']['static'] = 'cache.backend.null';
// $settings['cache']['bins']['views'] = 'cache.backend.null';

$settings['hash_salt'] = 'b18707a8a03f7a61198eefc64b409e8';
$settings['update_free_access'] = TRUE;

$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
];

$settings['entity_update_batch_size'] = 50;
$settings['entity_update_backup'] = TRUE;
$settings['file_private_path'] = '/app/private';
