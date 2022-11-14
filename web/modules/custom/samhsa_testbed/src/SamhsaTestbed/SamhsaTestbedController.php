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

    $orderId = '106205';

    $order = Order::load($orderId);
    $items = $order->getItems();

    // A working example
    foreach ($items as $item) {
      if ($item->hasPurchasedEntity()) {
        $purchasedItemId = $item->getPurchasedEntityId();

        $connection = \Drupal::database();
        // Sub query return Product entity ID from Variation ID
        $subQuery = $connection->select('commerce_product__variations', 'variations');
        $subQuery->condition('variations.variations_target_id', $purchasedItemId);
        $subQuery->fields('variations', ['entity_id']);

        // Main query, return GPO number from Entity ID.
        $query = $connection->select('commerce_product__field_gpo_pubcode', 'product_field');
        $query->condition('product_field.entity_id', $subQuery);
        $query->fields('product_field', ['field_gpo_pubcode_value']);

        $result = $query->execute()->fetchAll();
        dsm($result[0]->field_gpo_pubcode_value);
      }

    }




//    $orders = SamhsaXmlAPI::loadOrderIds($date);
//    foreach($orders as $order) {
//      dsm($order->order_id);
//    }

    $output = 'DEFAULT TEST OUTPUT';
    return array(
      '#markup' => $output,
    );
  }
}
