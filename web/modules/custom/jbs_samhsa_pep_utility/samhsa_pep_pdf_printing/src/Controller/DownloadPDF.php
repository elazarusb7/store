<?php

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
    $pdf_directory = \Drupal::config('samhsa_pep_pdf_printing.settings')->get('directory');
    $path = \Drupal::service('file_system')->realpath($pdf_directory . '/' . $file_name);

    if ($pdf_output = file_get_contents($path)) {
      $headers = [
      // Would want a condition to check for extension and set Content-Type dynamically.
        'Content-Type' => 'application/pdf',
        'Content-Description' => 'File Download',
        'Content-Disposition' => 'attachment; filename=' . $file_name,
      ];

      // Return and trigger file donwload.
      return new BinaryFileResponse($path, 200, $headers, TRUE);
    }
  }

}
