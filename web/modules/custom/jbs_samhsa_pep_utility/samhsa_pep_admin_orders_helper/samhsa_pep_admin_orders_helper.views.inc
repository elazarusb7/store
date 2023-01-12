<?php

/**
 * @file
 * Contains
 *   samhsa_pep_admin_orders_helper\samhsa_pep_admin_orders_helper.views.inc.
 *
 * Provides a custom views field data that isn't tied to any other module.
 */

/**
 * Implements hook_views_data().
 */
function samhsa_pep_admin_orders_helper_views_data() {
  $data['commerce_order']['yes_no_bulk_order_views_field'] = [
    'title' => t('Yes no bulk order views field'),
    'help' => t('Displays "Yes" or "No" to indicate an Order is a bulk or a default one'),
    'field' => [
      'id' => 'yes_no_bulk_order_views_field',
    ],
  ];
  return $data;
}
