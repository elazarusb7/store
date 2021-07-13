<?php
/**
 * @file Contains the code to generate custom drush commands.
 */

use Drupal\Core\Database\Database;

/**
 * Implements hook_drush_command().
 */
function samhsa_pep_migrate_custom_drush_command() {
  $items = array();
  $items['truncate-ul'] = [
    'description' => 'Truncate Unconverted Links table',
    'arguments' => [],
    'drupal dependencies' => ['samhsa_pep_migrate_custom'],
    'aliases' => ['truncate:unconverted_links'],
  ];
  return $items;
}

/**
 * Call back function for migrate_files_and_images_drush_command().
 */
function drush_samhsa_pep_migrate_custom_truncate_ul($limit = 0) {
  $db = Database::getConnection();
  if ($db->query('DELETE FROM unconverted_links;')) {
    Drush::output()->writeln('Unconverted Links table truncated. You can now uninstall migrate_custom module.');
  }
  else {
    Drush::output()->writeln('Could not truncate Unconverted Links table!');
  }
}
