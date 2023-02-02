<?php

/**
 * @file
 * Contains samhsa_pep_plugins\samhsa_pep_plugins.views.inc..
 * Provide a custom views field data that isn't tied to any other module. */

/**
 * Implements hook_views_data().
 */
function samhsa_pep_plugins_views_data() {
  $data['commerce_product_variation']['stock_status_field'] = [
    'title' => t('Stock Status Field'),
    'field' => [
      'title' => t('Stock Status Field'),
      'help' => t('Field to display Product Stock Status.'),
      'id' => 'stock_status_field',
    ],
  ];

  $data['commerce_product_variation']['product_pullets_field'] = [
    'title' => t('Product Pallets'),
    'field' => [
      'title' => t('Product Pallets Field'),
      'help' => t('Field to display Product Pallets.'),
      'id' => 'product_pullets_field',
    ],
  ];

  $data['commerce_product_variation']['product_pullets_count_field'] = [
    'title' => t('Product Pallets Count'),
    'field' => [
      'title' => t('Product Pallets Count'),
      'help' => t('Field to display Product Pallets Count.'),
      'id' => 'product_pullets_count_field',
    ],
  ];

  $data['commerce_product_variation']['display_mode_field'] = [
    'title' => t('Product Display Mode'),
    'field' => [
      'title' => t('Product Display Mode'),
      'help' => t('Field to display Product Display Mode.'),
      'id' => 'display_mode_field',
    ],
  ];

  return $data;
}
