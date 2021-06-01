<?php

/**
 * @file
 * Contains samhsa_pep_plugins\samhsa_pep_plugins.views.inc..
 * Provide a custom views field data that isn't tied to any other module. */

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Render\Markup;
use Drupal\field\FieldConfigInterface;
use Drupal\field\FieldStorageConfigInterface;
use Drupal\system\ActionConfigEntityInterface;

/**
 * Implements hook_views_data().
 */
function samhsa_pep_plugins_views_data() {
  $data['commerce_product_variation']['stock_status_field'] = array(
    'title' => t('Stock Status Field'),
    'field' => array(
      'title' => t('Stock Status Field'),
      'help' => t('Field to display Product Stock Status.'),
      'id' => 'stock_status_field',
    ),
  );

  $data['commerce_product_variation']['product_pullets_field'] = array(
    'title' => t('Product Pallets'),
    'field' => array(
       'title' => t('Product Pallets Field'),
       'help' => t('Field to display Product Pallets.'),
       'id' => 'product_pullets_field',
    ),
  );

  $data['commerce_product_variation']['product_pullets_count_field'] = array(
      'title' => t('Product Pallets Count'),
      'field' => array(
          'title' => t('Product Pallets Count'),
          'help' => t('Field to display Product Pallets Count.'),
          'id' => 'product_pullets_count_field',
      ),
  );

  $data['commerce_product_variation']['display_mode_field'] = array(
      'title' => t('Product Display Mode'),
      'field' => array(
          'title' => t('Product Display Mode'),
          'help' => t('Field to display Product Display Mode.'),
          'id' => 'display_mode_field',
      ),
  );

  return $data;
}