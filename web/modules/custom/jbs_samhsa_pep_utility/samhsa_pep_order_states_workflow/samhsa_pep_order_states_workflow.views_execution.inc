<?php

/**
 * @file
 * Contains samhsa_pep_order_states_workflow\samhsa_pep_order_states_workflow.views_execution.inc..
 *
 * Provide additional customization to rendered Views.
 */

use Drupal\views\ViewExecutable;

/**
 * Implements hook_views_pre_view().
 */
function samhsa_pep_order_states_workflow_views_pre_view(ViewExecutable $view, $display_id, array &$args) {
  // Cart page. calculate total items.
  if ($view->id() == 'commerce_cart_form') {
    // Footer with items qty count total.
    $carts = \Drupal::service('commerce_cart.cart_provider')
      ->getCarts();
    if (is_array($carts) && count($carts) == 1) {
      $cart = array_shift($carts);
      $items = $cart->getItems();
      $total = 0;
      foreach ($items as $key => $item) {
        $total += $item->getQuantity();
      }

      $options = [
        'id' => 'area_text_custom',
        'table' => 'views',
        'field' => 'area_text_custom',
        'relationship' => 'none',
        'group_type' => 'none',
        'admin_label' => '',
        'empty' => TRUE,
        'tokenize' => FALSE,
        'content' => ($total == 0 || $total > 1) ? $total . ' items' : $total . ' item',
        'plugin_id' => 'text_custom',
      ];
      $view->setHandler($display_id, 'footer', 'area_text_custom', $options);
    }
  }

  if ($view->id() == 'order_information_report' && $display_id === 'page_1') {
    // Header with link to CSV.
    $options = [
      'id' => 'area_text_custom',
      'table' => 'views',
      'field' => 'area_text_custom',
      'relationship' => 'none',
      'group_type' => 'none',
      'admin_label' => '',
      'empty' => TRUE,
      'tokenize' => FALSE,
      'content' => _samhsa_pep_order_states_workflow_build_link_to_csv($view) ,
      'plugin_id' => 'text_custom',
    ];
    $view->setHandler($display_id, 'header', 'area_text_custom', $options);
    // Footer with calculated totals.
    $options = [
      'id' => 'area_text_custom',
      'table' => 'views',
      'field' => 'area_text_custom',
      'relationship' => 'none',
      'group_type' => 'none',
      'admin_label' => '',
      'empty' => TRUE,
      'tokenize' => FALSE,
      'content' => _samhsa_pep_order_states_workflow_build_total_bulk($view),
      'plugin_id' => 'text_custom',
    ];
    $view->setHandler($display_id, 'footer', 'area_text_custom', $options);
  }
}

/**
 * Build a header with the link to the CSV.
 *
 * @param \Drupal\views\ViewExecutable $view
 *   View object.
 *
 * @return string
 *   Content to be displayed in the footer.
 */
function _samhsa_pep_order_states_workflow_build_link_to_csv(ViewExecutable $view) {
  $url_parameters = urldecode($view->getRequest()->getQueryString());
  return '<div><a href="/admin/order-information-report/export' .
    $url_parameters .
    '">Download this data in CSV format</a></div>';
}

/**
 * Build a footer with the total of bulk orders.
 *
 * @param \Drupal\views\ViewExecutable $view
 *   View object.
 *
 * @return string
 *   Content to be displayed in the footer.
 */
function _samhsa_pep_order_states_workflow_build_total_bulk(ViewExecutable $view) {
  $query = urldecode($view->getRequest()->getQueryString());
  $filters = [];
  if ($query != '') {
    $parameters = explode('&', $query);
    foreach ($parameters as $parameter) {
      if ($pieces = explode('=', $parameter)) {
        $filters[$pieces[0]] = trim($pieces[1]);
      }
    }
  }
  $total = _samhsa_pep_order_states_workflow_calculate_total_bulk($filters);
  return "<div>Total of Bulk Orders: $total</div>";
}

/**
 * Gets the total of bulk orders matching the filter parameters.
 *
 * @param array $filters
 *   Parameters to build the query.
 *
 * @return int
 *   Number or bulk orders matching the filter parameters.
 */
function _samhsa_pep_order_states_workflow_calculate_total_bulk(array $filters) {
  $query = \Drupal::database()->select('commerce_order', 't');
  $query->fields('t', ['p1', 'p2', 'score']);
  // Bulk Orders.
  $query->condition('type', 'samhsa_publication_ob', '=');
  // Order number.
  if (isset($filters['order_number'])) {
    $query->condition('order_number', $filters['order_number'], 'LIKE');
  }
  // Completed.
  if (isset($filters['completed'])) {
    $query->condition('completed', strtotime($filters['completed']), '>=');
  }
  if (isset($filters['completed_1'])) {
    $query->condition('completed', strtotime($filters['completed_1']), '<=');
  }
  // Placed.
  if (isset($filters['placed'])) {
    $query->condition('placed', strtotime($filters['placed']), '>=');
  }
  if (isset($filters['placed_1'])) {
    $query->condition('placed', strtotime($filters['placed']), '<=');
  }
  $result = $query->countQuery()->execute()->fetchField();
  return $result;
}
