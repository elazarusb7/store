<?php
/**
 * Created by PhpStorm.
 * User: vlyalko
 * Date: 12/13/19
 * Time: 1:01 PM
 */

namespace Drupal\samhsa_pep_pdf_printing;

use Drupal\Component\Utility\Random;
use Drupal\Core\Link;

class PrintInvoices {

  public static function printInvoicesInBatch($data, &$context) {
    //$pdf_directory = \Drupal::service('file_system')->realpath("public://pdf");
    $pdf_directory = \Drupal::config('samhsa_pep_pdf_printing.settings')->get('directory');

    if (\Drupal::service('file_system')->prepareDirectory($pdf_directory, \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY | \Drupal\Core\File\FileSystemInterface::MODIFY_PERMISSIONS)){
      $nameGenerator = new Random();
      $file_name = $nameGenerator->string();
      $file_name ='pickslip_' . date('m-d-Y_hia').'.pdf';
      \Drupal::service('samhsa_pep_pdf_printing.invoice')->generateInvoices($data['orders'], $data['is_returned_order'], $file_name);
      $context['message'] = t('Printing Pick Slips');
      $context['results'] = [
        'orders' => count($data['orders']),
        'file_name' => $file_name,
      ];
    }
  }

  public static function finishedPrintingInvoicesCallback($success, $results, $operations) {
    if ($success) {
      $download_link = Link::createFromRoute('Download PDF', 'samhsa_pep_operations.download', ['file_name' => $results['file_name']]);
      //ksm($download_link);
      $text_link = $download_link->toString();
      $message = \Drupal::translation()->formatPlural(
        $results['orders'],
        'One Pick Slip printed: ' . $text_link, '@count Pick Slips printed: ' . $text_link
      );
        //$message = t('Finished without an error.');
        \Drupal::messenger()->addMessage($message,'status');
    }
    else {
      $message = t('Finished with an error.');
        \Drupal::messenger()->addMessage($message,'error');
    }
  }


}