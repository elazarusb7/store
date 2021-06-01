<?php

/**
 * @file
 * Contains \Drupal\samhsa_pep_operations\Controller\DownloadPDF.
 */

namespace Drupal\samhsa_pep_pdf_printing\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
/**
 * Class DownloadPDF.
 *
 * @package Drupal\samhsa_pep_pdf_printing\Controller
 */
class DownloadPDF extends ControllerBase {

  /**
   * Execute the PDF downloading.
   */
  public function execute($file_name) {
    //$pdf_directory = \Drupal::service('file_system')->realpath("public://pdf");

    $pdf_directory = \Drupal::config('samhsa_pep_pdf_printing.settings')->get('directory');
    //$path = drupal_realpath($pdf_directory . '/' . $file_name);
    $path = \Drupal::service('file_system')->realpath($pdf_directory . '/' . $file_name);

    if ($pdf_output = file_get_contents($path)) {
      //header('Content-Description: File Transfer');
      //header('Content-Type: application/pdf');
      //header('Content-Disposition: attachment; filename="Order_Pick_Slip_'. date('m-d-Y_hia').'.pdf"');
      //header('Content-Length: ' . strlen($pdf_output));
      //echo $pdf_output;
      //return true;

        $headers = [
            'Content-Type' => 'application/pdf', // Would want a condition to check for extension and set Content-Type dynamically
            'Content-Description' => 'File Download',
            'Content-Disposition' => 'attachment; filename=' . $file_name
        ];

        // Return and trigger file donwload.
        return new BinaryFileResponse($path, 200, $headers, true );
    }
  }

}
