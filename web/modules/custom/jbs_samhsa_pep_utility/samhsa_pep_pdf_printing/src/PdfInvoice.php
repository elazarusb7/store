<?php

namespace Drupal\samhsa_pep_pdf_printing;

/**
 * Class PdfInvoice.
 *
 * @package Drupal\samhsa_pep_pdf_printing
 */
class PdfInvoice {

  /**
   * Constructor.
   */
  public function __construct() {

  }

  /**
   * Generates a PDF with the Invoice.
   *
   * @param $orders
   *   Orders contained in the invoice.
   * @param $file_name
   * @param false $is_returned_order
   *   Whether the Order ia a returned one.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function generateInvoices($orders, $file_name, $is_returned_order = FALSE) {
    $pdf = new PepFPDF();
    $pdf->AliasNbPages();
    $pdf->setIsReturnedOrder($is_returned_order);
    $this->coverPageForAllOrders($pdf, $orders);

    $this->invoicesPages($pdf, $orders);
    $pdf_output = $pdf->Output('S');
    // $pdf_output = $pdf->Output('F'); //NOT WORKING
    // $pdf_directory = \Drupal::service('file_system')->realpath("public://pdf");
    $pdf_directory = \Drupal::config('samhsa_pep_pdf_printing.settings')
      ->get('directory');

    /*will create file and create record infile_managed table*/
    $file = \Drupal::service('file.repository')
      ->writeData($pdf_output, $pdf_directory . '/' . $file_name);
  }

  /**
   * Builds the cover page for the whole batch.
   *
   * @param \FPDF $pdf
   *   PDF object.
   * @param array $orders
   *   Orders contained in the invoice.
   */
  private function coverPageForAllOrders(\FPDF $pdf, $orders) {
    $pdf->StartPageGroup();
    $pdf->SetFont('Arial', '', 12);
    $pdf->setContentSection('summarypage');
    $pdf->AddPage();

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetY($pdf->GetY());
    $margin = 15;
    $pdf->SetLeftMargin($margin);
    $pdf->SetRightMargin($margin);

    $pdf->Cell(0, 10, 'Pick ' . (count($orders) > 1 ? 'Slips ' : 'Slip ') . 'printed on: ' . date('m-d-y h:i A'), 0, 0, 'R');
    $pdf->SetY($pdf->GetY() + 15);

    $page_width = $pdf->GetPageWidth();
    // $margin = ($page_width - 150) / 2;
    $pdf->SetFillColor(230);
    $pdf->setX($margin);
    $full_page_width = $pdf->GetPageWidth();
    $page_width = $full_page_width - ($margin * 2);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(7, 10, "#", 0, 0, 'C', FALSE);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(26, 10, "Order #", 0, 0, 'C', FALSE);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(($full_page_width - (2 * $margin)) - 33, 10, "Customer Name", 0, 1, 'L', FALSE);
    $pdf->SetY($pdf->GetY() + 1);
    $pdf->Line($margin, $pdf->GetY(), $full_page_width - $margin, $pdf->GetY());
    foreach ($orders as $number => $order) {
      $order_id = $order->id();
      $shipping = \Drupal::service('samhsa_pep_pdf_printing.label')
        ->getShipping($order_id);
      if ($shipping) {
        $address_value = $shipping->get('address')->getValue();
        $address = array_shift($address_value);
        $pdf->SetFillColor(230);
        $fname = $address['given_name'];
        $lname = $address['family_name'];
      }
      else {
        $pdf->SetFillColor(67, 187, 70);
        $fname = "missing";
        $lname = "shipping information";
      }
      $pdf->setX($margin);
      $bg = ($number % 2) ? TRUE : FALSE;
      $pdf->SetFont('Arial', '', 12);
      $pdf->Cell(7, 10, ($number + 1), 0, 0, 'C', $bg);
      $pdf->SetFont('Arial', 'B', 12);
      $pdf->Cell(26, 10, $order_id, 0, 0, 'C', $bg);
      $pdf->SetFont('Arial', '', 12);
      $pdf->Cell(($full_page_width - (2 * $margin)) - 33, 10, $fname . chr(32) . $lname, 0, 1, 'L', $bg);
    }
    $pdf->setContentSection('summaryfooter');
  }

  /**
   * Builds the order cover page for internal use only.
   *
   * @param \FPDF $pdf
   *   PDF object.
   * @param entity $order
   *   Orders contained in the invoice.
   */
  private function coverPage(\FPDF $pdf, $order) {
    $pdf->StartPageGroup();
    $pdf->AddPage();
    $pdf->setContentSection('cover');

    $page_width = $pdf->GetPageWidth();
    $margin = ($page_width - 150) / 2;
    $pdf->SetFillColor(230);

    // Customer Email starts //.
    $pdf->SetFont('Arial', 'B', 12);

    $pdf->SetX($margin);
    $pdf->SetY($pdf->GetY() + 15);
    $pdf->Write(8, 'Customer Email:');
    $pdf->SetY($pdf->GetY() + 7);
    $pdf->PutLink($order->getEmail(), $order->getEmail());
    $pdf->SetY($pdf->GetY() + 10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Write(8, 'Tracking ID:');
  }

  /**
   * Builds the invoices pages.
   *
   * @param \FPDF $pdf
   *   PDF object.
   * @param array $orders
   *   Orders contained in the invoice.
   */
  private function invoicesPages(\FPDF $pdf, $orders) {
    $margin = 10;
    $pdf->SetLeftMargin($margin);
    $pdf->SetRightMargin($margin);
    $full_page_width = $pdf->GetPageWidth();
    $page_width = $full_page_width - ($margin * 2);
    // $y_top = $pdf->getYTopItems();
    // $pdf->SetY($y_top);
    foreach ($orders as $order) {
      $pdf->setOrder($order);

      $created = date("m/d/Y", $order->getPlacedTime());
      // $received = date("l jS \of F Y h:i:s A", $order->getPlacedTime());
      $received = date("m/d/Y H:i:s A", $order->getPlacedTime());

      if (!empty(\Drupal::hasService('samhsa_pep_utility.pep_utility_functions'))) {

        $GLOBALS["order_info"] = [
          $order->id(),
          \Drupal::service('samhsa_pep_utility.pep_utility_functions')
            ->getOrderSource($order),
          $created,
          '',
        ];
      }

      $this->coverPage($pdf, $order);

      $pdf->StartPageGroup();
      $pdf->AddPage();
      $pdf->setContentSection('invoice');

      // Header starts //.
      $pdf->SetFont('Arial', 'B', 12);
      $pdf->SetY($pdf->GetY() + 5);
      $margin = 10;
      $pdf->SetLeftMargin($margin);
      $pdf->SetRightMargin($margin);
      // $this->SetTopMargin(50);
      $x = $pdf->GetX();
      $y = $pdf->GetY();
      $packingsliptitle = "PACKING SLIP";
      $pdf->Cell(80, 10, $packingsliptitle, 0, 'l', FALSE);
      $pdf->SetXY($x + 80, $y);
      $pdf->SetFont('Arial', '', 12);
      $pdf->Cell(180, 10, 'Received: ' . $received, 0, 'l', FALSE);

      $pdf->SetY($pdf->GetY() + 15);
      $pdf->setX($margin);
      $pdf->SetFont('Arial', 'B', 12);

      $title_w = $page_width - 60;
      $pdf->Cell(40, 10, 'Publication ID', 0, 0, 'L');
      $pdf->Cell($title_w, 10, 'Title', 0, 0, 'L');
      $pdf->Cell(20, 10, 'Quantity', 0, 1, 'R');
      $y = $pdf->GetY();
      $pdf->Line($margin, $y, $full_page_width - $margin, $y);

      $pdf->SetFillColor(230);
      $pdf->SetFont('Arial', '', 10);
      $data = [];
      foreach ($order->getItems() as $number => $item) {
        $variation = $item->getPurchasedEntity();
        if (isset($variation)) {
          $data[] = [
            'sku' => $variation->getSku(),
            'title' => iconv('UTF-8', 'windows-1252', stripslashes($variation->getTitle())),
            'qty' => round($item->getQuantity()),
          ];
        }
        else {
          // There was some issue in getting the variant.
          $data[] = [
            'sku' => 'not found: ' . $item->id(),
            'title' => $item->getTitle(),
            'qty' => round($item->getQuantity()),
          ];
        }
      }
      $data_sorted = \Drupal::service('samhsa_pep_pdf_printing.label')
        ->array_msort($data, [
          'sku' => SORT_ASC,
          'title' => SORT_ASC,
          'qty' => SORT_ASC,
        ]);
      foreach ($data_sorted as $item) {
        $quantity = $item['qty'];
        $sku = $item['sku'];
        $cleanedTitle = $item['title'];

        $title_w = $page_width - 60;
        // $bg = ($number % 2) ? TRUE : FALSE;
        $bg = FALSE;

        $x_axis = $pdf->getx();
        $c_width = 40;
        $c_height = 10;
        $pdf->vcell($c_width, $c_height, $x_axis, $sku, 0, 0, 'L', FALSE, 15);
        $x_axis = $pdf->getx();
        $title_w = $page_width - 60;
        $c_width = $title_w;
        $c_height = 10;
        $pdf->vcell($c_width, $c_height, $x_axis, $cleanedTitle, 0, 0, 'L', FALSE, 75);
        $x_axis = $pdf->getx();
        $c_width = 20;
        $c_height = 10;
        $pdf->vcell($c_width, $c_height, $x_axis, $quantity, 0, 0, 'R', FALSE, 75);
        $pdf->Ln();
      }

      $x = $pdf->GetX();
      $y = $pdf->GetY();
      $pdf->Line($margin, $y, $full_page_width - $margin, $y);
      $y = $pdf->GetY();
      $footerText = $pdf->getFooterText();
      $returnedAddress = $pdf->getReturnedAddress();
      $returned_address_text = "";
      foreach ($returnedAddress as $address_line) {
        $returned_address_text .= $address_line . chr(10);
      }

      // $pdf->AddPage();
      // Returned Address starts //
      $pdf->SetFont('Arial', 'B', 12);

      $pdf->SetX($margin);
      $pdf->SetY($pdf->GetY() + 15);
      $pdf->Write(8, 'Return Address');

      $pdf->SetFont('Arial', '', 12);
      $pdf->SetY($pdf->GetY() + 10);
      $margin = 25;
      $pdf->SetLeftMargin($margin - 15);
      $pdf->SetRightMargin($margin - 15);
      // $pdf->SetTopMargin(50);
      $x = $pdf->GetX();
      $y = $pdf->GetY();
      $pdf->MultiCell(120, 5, $returned_address_text, 0, 'l', FALSE);

      // Footer text starts.
      $pdf->SetY($pdf->GetY() + 10);
      $pdf->SetFont('Arial', '', 12);
      $pdf->WriteHTML(utf8_decode($footerText));
      $pdf->Ln();
    }
  }

}
