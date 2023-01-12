<?php

namespace Drupal\samhsa_pep_pdf_printing;

use Drupal\commerce_shipping\Entity\Shipment;

/**
 * Class PdfPickSlips.
 *
 * @package Drupal\samhsa_pep_pdf_printing
 */
class PdfPickSlips {

  /**
   * Constructor.
   */
  public function __construct() {

  }

  /**
   * Generates a PDF with labels.
   *
   * @param array $label_rows
   *   The labels.
   */
  public function generateLabels($label_rows) {

    $config = \Drupal::config('samhsa_pep_pdf_printing.config');

    $left_margin_col_1 = $config->get('left_margin_col_1');
    $left_margin_col_2 = $config->get('left_margin_col_2');
    $top_margin = $config->get('top_margin');
    $label_height = $config->get('label_height');
    $label_width = $config->get('label_width');
    $line_height = $config->get('line_height');
    $rows_per_page = $config->get('rows_per_page');
    $font_name = $config->get('font_name');
    $font_style = $config->get('font_style');
    $font_size = $config->get('font_size');

    $pdf = new \FPDF('P', 'mm', [215.9, 279.4]);
    $pdf->AddPage();
    $pdf->SetFont($font_name, $font_style, $font_size);

    $page_row = 0;
    $batch_row = 0;
    $total_batch_rows = count($label_rows);
    foreach ($label_rows as $label_row) {
      $y = ($label_height * $page_row) + $top_margin;

      // // Rectangles for testing purpose.
      //      $pdf->Rect($left_margin_col_1, $y, $label_width, $label_height);
      //      $pdf->Rect($left_margin_col_2, $y, $label_width, $label_height);
      $text_col_1 = NULL;
      $text_col_2 = NULL;

      for ($i = 0; $i <= 6; $i++) {
        if (isset($label_row['col_1'][$i])) {
          $text_col_1 .= $label_row['col_1'][$i] . "\n";
        }
        if (isset($label_row['col_2'][$i])) {
          $text_col_2 .= $label_row['col_2'][$i] . "\n";
        }
      }

      $pdf->SetLeftMargin($left_margin_col_1);
      $pdf->SetX($left_margin_col_1);
      $pdf->SetY($y);
      $pdf->MultiCell(100, $line_height, $text_col_1, 0, 'L');

      $pdf->SetLeftMargin($left_margin_col_2);
      $pdf->SetX($left_margin_col_2);
      $pdf->SetY($y);
      $pdf->MultiCell(100, $line_height, $text_col_2, 0, 'L');

      $page_row++;
      $batch_row++;
      if ($page_row >= $rows_per_page && $batch_row < $total_batch_rows) {
        $pdf->AddPage();
        $page_row = 0;
      }
    }
    $pdf_output = $pdf->Output('S');
    header('Content-Description: File Transfer');
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="Order_Labels.pdf"');
    header('Content-Length: ' . strlen($pdf_output));
    echo $pdf_output;
    flush();
  }

  /**
   * Generates a PDF for testing purposes.
   *
   * @param int $number_of_rows
   *   Number of rows to be printed.
   */
  public function generateTestLabels($number_of_rows) {
    $samples = [
          [
            'aaaaaaaaaa',
            'bbbbbbbbbb',
            'cccccccccc',
            'dddddddddd',
            'eeeeeeeeee',
            'ffffffffff',
          ],
          [
            '1111111111',
            '2222222222',
            '3333333333',
            '4444444444',
            '5555555555',
            '6666666666',
            '7777777777',
          ],
          [
            'JBS International',
            'Web Solutions',
            '5515 Security Ln',
            'Suite 800',
            'North Bethestda, MD',
          ],
          [
            'JBS International',
            'Warehouse',
            '3333333333',
            '8386 Bristol Ct',
            'Jessup, MD 20794',
          ],
    ];
    $labels = [];
    for ($i = 1; $i <= $number_of_rows; $i++) {
      $labels[] = [
        'col_1' => $samples[rand(0, 3)],
        'col_2' => $samples[rand(0, 3)],
      ];
    }
    $this->generateLabels($labels);
  }

  /**
   * Gets the order shipping profile.
   *
   * @param int $order_id
   *   Shipping ID.
   *
   * @return entity
   *   The shipping profile.
   */
  public function getShipping($order_id) {
    $result = $this->getShippingByOrderId($order_id);
    return $result;
  }

  /**
   * Sets a query condition to search for a given order ID.
   *
   * @param int $order_id
   *   The order ID.
   *
   * @return array
   *   An associative array with the shipping info.
   */
  public function getShippingByOrderId($order_id) {
    $order = \Drupal::entityTypeManager()->getStorage('commerce_order')->load($order_id);
    $order_shipments = $order->get('shipments');
    // @todo for now, get only primary shipment(first one).
    $count = 0;
    foreach ($order_shipments->getValue() as $order_shipment) {
      $count++;
      if ($order_shipment['target_id']) {
        $ship_id = $order_shipment['target_id'];
        $shipment = Shipment::load($ship_id);
        $shipment_profile = $shipment->getShippingProfile();
      }
      if ($count == 1) {
        break;
      }
    }
    return $shipment_profile;
  }

  /**
   *
   */
  public function array_msort($array, $cols) {
    $colarr = [];
    foreach ($cols as $col => $order) {
      $colarr[$col] = [];
      foreach ($array as $k => $row) {
        $colarr[$col]['_' . $k] = strtolower($row[$col]);
      }
    }
    $eval = 'array_multisort(';
    foreach ($cols as $col => $order) {
      $eval .= '$colarr[\'' . $col . '\'],' . $order . ',';
    }
    $eval = substr($eval, 0, -1) . ');';
    eval($eval);
    $ret = [];
    foreach ($colarr as $col => $arr) {
      foreach ($arr as $k => $v) {
        $k = substr($k, 1);
        if (!isset($ret[$k])) {
          $ret[$k] = $array[$k];
        }
        $ret[$k][$col] = $array[$k][$col];
      }
    }
    return $ret;

  }

}
