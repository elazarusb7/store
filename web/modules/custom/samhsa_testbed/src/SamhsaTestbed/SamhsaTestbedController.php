<?php
/**
 * @file
 * Contains \Drupal\samhsa_testbed\SamhsaTestbed\SamhsaTestbedController
 */

namespace Drupal\samhsa_testbed\SamhsaTestbed;

use Drupal\commerce_order\Entity\Order;
use Drupal\Core\Controller\ControllerBase;
use Drupal\samhsa_xml\API\SamhsaXmlAPI;

class SamhsaTestbedController extends ControllerBase {
  public function __construct() {}

  public function testbed() {
    // Test code here


    $output = 'DEFAULT TEST OUTPUT';
    return array(
      '#markup' => $output,
    );
  }
}
