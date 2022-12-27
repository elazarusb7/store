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
    $fid = 7121;
    $f = File::load($fid);
    $fullURL = \Drupal::request()->getSchemeAndHttpHost() . $f->createFileUrl();

    $file = fopen($fullURL, "r");
    $out = [];
        while (!feof($file)) {
          $out[] = fgetcsv($file)[0];
        }
    dsm($out);

    $output = 'DEFAULT TEST OUTPUT';
    return array(
      '#markup' => $output,
    );
  }
}
