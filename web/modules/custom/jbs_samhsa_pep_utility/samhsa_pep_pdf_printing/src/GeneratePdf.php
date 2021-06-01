<?php

/**
 * @file
 * Contains \Drupal\samhsa_pep_pdf_printing\GeneratePdf.
 */

namespace Drupal\samhsa_pep_pdf_printing;

use Drupal\Core\Database\Database;

/**
 * Class Status.
 *
 * @package Drupal\samhsa_pep_pdf_printing
 */
class GeneratePdf {

  /**
   * Constructor.
   */
  public function __construct() {

  }

  /**
   * Generated the PDF labels.
   *
   * @param array $orders_ids
   *   List of Orders IDs to be printed.
   */
  public function labels($orders_ids) {
    $label_rows = [];
    $row = [];
    foreach ($orders_ids as $key => $order_id) {
      $label_ines = $this->formatLabelLines($order_id);
      if ($key % 2) {
        $row['col_2'] = $label_ines;
        $label_rows[] = $row;
        $row = [];
      }
      else {
        $row['col_1'] = $label_ines;
      }
    }
    if (!($key % 2)) {
      $row['col_2'] = [];
      $label_rows[] = $row;
      $row = [];
    }
    \Drupal::service('samhsa_pep_pdf_printing.label')->generateLabels($label_rows);
  }

  /**
   * Gets the Order and Formats the array with the label lines.
   *
   * @param int $order_id
   *   Order ID.
   *
   * @return array
   *   The lines of the label.
   */
  private function formatLabelLines($order_id) {
    $order = \Drupal::service('samhsa_pep_pdf_printing.label')->getShipping($order_id);
    $result = [
      /*$order['first_name'] . chr(32) . $order['last_name'],
      $order['organization'],
      $order['address_1'],
      $order['address_2'],
      $order['city'] . ', ' . $order['state'] . chr(32) . $order['zip'],*/
        'first last',
        'organization',
        'address_1',
        'address_2',
        'Baltimore MD 21136',
    ];
    $result = array_values(array_filter($result, [$this, 'filterLines']));
    return $result;
  }

  /**
   * Callback for array_filter().
   *
   * Removes empty elements and other undesired values.
   *
   * @param string $element
   *   Element to be checked.
   *
   * @return bool
   *   Whether or not the string should be kept.
   */
  private function filterLines($element) {
    $element = trim($element);
    if (!$element) {
      return FALSE;
    }
    elseif (empty($element)) {
      return FALSE;
    }
    elseif ($element == '- None -' || $element == '_none') {
      return FALSE;
    }
    else {
      return TRUE;
    }
  }

  /**
   * Generated the PDF invoice.
   *
   * @param array $orders_ids
   *   List of Orders IDs to be printed.
   * @param bool $is_returned_order
   *   Whether the Order ia a returned one.
   */
  public function invoice($orders_ids, $is_returned_order = FALSE) {
    $orders = [];
    foreach ($orders_ids as $key => $order_id) {
        $orders[] = \Drupal::entityTypeManager()->getStorage('commerce_order')->load($order_id);
    }

    $batch = array(
      'title' => t('Printing Pick Slips...'),
      'operations' => [
        [
          '\Drupal\samhsa_pep_pdf_printing\PrintInvoices::printInvoicesInBatch',
          [['orders' => $orders, 'is_returned_order' => $is_returned_order]],
        ],
      ],
      'finished' => '\Drupal\samhsa_pep_pdf_printing\PrintInvoices::finishedPrintingInvoicesCallback',
    );
    batch_set($batch);
  }


}
