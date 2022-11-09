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

//    $dateParts = explode('-', '2022-05-15');
//    $startTime = mktime('00', '00', '00', $dateParts[1], $dateParts[2], $dateParts[0]);
//    $endTime = mktime('23', '59', '59', $dateParts[1], $dateParts[2], $dateParts[0]);
//    $connection = \Drupal::database();
//    $query = $connection->select('commerce_order', 'ca');
//    $query->condition('ca.created', $startTime, '>');
//    $query->condition('ca.created', $endTime, '<');
//    $query->fields('ca', ['order_id']);
//
//    $orders = $query->execute()->fetchAllAssoc('order_id');
//    dpm($orders);

    $order_id = 62193;
    $order = Order::load($order_id);
    $orderDateTime = date('c', $order->getPlacedTime());
    $orderNumber = $order->getOrderNumber();
    dsm($orderNumber);
    $customer = $order->getCustomer();
    $customerEmail = $customer->getEmail();
//    dsm($customerEmail);
    $profile = $order->getBillingProfile();
//    $address = $profile->get('address')->getValue()[0];
//    dsm($address);
    $phone = $profile->get('field_phone_number')->getValue()[0]['value'];
//    dsm($phone);
    $items = $order->getItems();
    foreach($items as $item) {
      $itemTitle = $item->getTitle();
    }
//    dsm(get_class_methods($order));


    $output = 'DEFAULT TEST OUTPUT';
    return array(
      '#markup' => $output,
    );
  }
}
