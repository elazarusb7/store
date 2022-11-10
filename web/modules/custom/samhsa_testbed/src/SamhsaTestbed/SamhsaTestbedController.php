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

    $date = '2022-05-16';
//    $dateParts = explode('-', $date);
//    $startTime = mktime('00', '00', '00', $dateParts[1], $dateParts[2], $dateParts[0]);
//    $endTime = mktime('23', '59', '59', $dateParts[1], $dateParts[2], $dateParts[0]);
//    $connection = \Drupal::database();
//    $query = $connection->select('commerce_order', 'ca');
//    $query->condition('ca.created', $startTime, '>');
//    $query->condition('ca.created', $endTime, '<');
//    $query->condition('ca.uid', 1, '>');
//    $query->fields('ca', ['order_id']);

    $orders = SamhsaXmlAPI::loadOrderIds($date);
    foreach($orders as $order) {
      dsm($order->order_id);
    }

    $output = 'DEFAULT TEST OUTPUT';
    return array(
      '#markup' => $output,
    );
  }
}
