<?php
/**
 * @file
 * Contains \Drupal\samhsa_testbed\SamhsaTestbed\SamhsaTestbedController
 */

namespace Drupal\samhsa_testbed\SamhsaTestbed;

use Drupal\commerce_order\Entity\Order;
use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;
use Drupal\samhsa_xml\API\SamhsaXmlAPI;

class SamhsaTestbedController extends ControllerBase {
  public function __construct() {}

  public function testbed() {
    // Test code here
    $csvFid = 7129;
    $jsonFid = 7130;
    $xmlFid = 7131;
    $fileObj = File::load($csvFid);

    SamhsaXmlAPI::processFulfilledOrder($fileObj);
//    dsm($fileObj);

//    $fullURL = \Drupal::request()->getSchemeAndHttpHost() . $fileObj->createFileUrl();
//    $mimeType = $fileObj->getMimeType();
//    $output = [];
//    if ($mimeType === 'text/csv') {
//      $f = fopen($fullURL, "r");
//      while (!feof($f)) {
//        $output[] = fgetcsv($f)[0];
//      }
//      fclose($f);
//    }
//    else if ($mimeType === 'application/octet-stream') {
//      // JSON
//      $f = file_get_contents($fullURL);
//      $output = json_decode($f, true);
//    }
//    else if ($mimeType === 'application/xml') {
//      $f = file_get_contents($fullURL);
//      $new = simplexml_load_string($f);
//      $con = json_encode($new);
//      $output = json_decode($con, true)['order'];
//    }
//
//    dsm($output);

//    $array = ['12345','23456','56789'];
//    $json = json_encode($array);
//    dsm($json);

    $output = 'DEFAULT TEST OUTPUT';
    return array(
      '#markup' => $output,
    );
  }
}
