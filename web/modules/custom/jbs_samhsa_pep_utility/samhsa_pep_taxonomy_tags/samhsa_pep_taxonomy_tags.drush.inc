<?php

use Drupal\samhsa_pep_taxonomy_tags\controller\migrate;

/**
 * Implements hook_drush_command().
 */
function samhsa_pep_taxonomy_tags_drush_command() {
  $commands = [];
  $commands['taxonomy-migrate'] = [
    'description' => 'Migrate PEP Product tags from Tags vocabulary to proper vocabularies.',
    'aliases'     => ['tmig'],
  ];
  return $commands;
}
/**
 * Call back function drush_custom_drush_command_say_hello()
 * The call back function name in the  following format
 *   drush_{module_name}_{item_id_for_command}()
 */
function drush_samhsa_pep_taxonomy_tags_taxonomy_migrate() {
  // Your codes
  //return 'hello';
  //$migrate = new migrate();
  //return "Taxonomy Tag migration: " . $migrate->getStatus();
}
