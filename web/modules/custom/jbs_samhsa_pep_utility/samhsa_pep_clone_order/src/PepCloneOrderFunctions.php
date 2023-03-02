<?php

namespace Drupal\samhsa_pep_clone_order;

use Drupal\commerce_shipping\ShipmentItem;
use Drupal\commerce_shipping\Entity\Shipment;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
use Symfony\Component\HttpFoundation\Request;
use Drupal\physical\Weight;
use Drupal\physical\WeightUnit;

/**
 * Class PepUtilityFunctions.
 *
 * @package Drupal\samhsa_pep_clone_order
 */
class PepCloneOrderFunctions implements PepCloneOrderFunctionsInterface {

  /**
   * Constructor.
   */
  public function __construct() {
    $this->basePath = getcwd();
    $request = Request::createFromGlobals();
    $this->baseUrl = $request->getBaseUrl();
  }

  /**
   * {@inheritdoc}
   */
  public function cloneOrder($order = NULL) {
    $entity = $order;
    $mail = $entity->getEmail();
    $customer = $entity->getCustomer()->id();
    $billing_profile = $entity->getBillingProfile();
    $items = $entity->getItems();

    // Line items.
    $line_items = [];

    foreach ($items as $item) {
      $quantity = $item->getQuantity();
      $product = $item->getPurchasedEntity();

      $order_item = OrderItem::create([
        'type' => 'default',
        'purchased_entity' => $product,
        'quantity' => $quantity,
        'unit_price' => 0,
      ]);

      $order_item->save();
      array_push($line_items, $order_item);

    }

    // Create order.
    $order = Order::create([
      'type' => $entity->bundle(),
      'store_id' => 1,
      'checkout_flow' => 'complete',
      'uid' => $customer,
      'billing_profile' => $billing_profile,
      'placed' => time(),
      'field_justification' => $entity->get('field_justification')->value,
      'field_log' => 'This order cloned from ' . $entity->id(),
      'order_items' => $line_items,
      'state' => 'draft',
      'mail' => $mail,
          // 'completed' => time(),
    ]);

    $order->save();
    $order->set('order_number', $order->id());
    $order->save();
    $order->set('state', 'pending');
    $order->save();

    $comment = 'This order cloned from order#: ' . $entity->id();
    $logStorage = \Drupal::entityTypeManager()->getStorage('commerce_log');
    $logStorage->generate($order, 'order_comment', ['comment' => $comment])->save();

    // Add shipments.
    $shipments = $entity->get('shipments');
    if (is_array($shipments->getValue())) {
      $ship_ids = $shipments->getValue();
      $shipment_ids = array_shift($ship_ids);
    }
    /*TODO: update foreach loop for shipments*/
    /*foreach ($order_shipments->getValue() as $order_shipment) {
    if ($order_shipment['target_id']) {
    ksm($order_shipment['target_id']);
    }
    }*/
    if (is_array($shipment_ids)) {
      foreach ($shipment_ids as $shipment_id) {
        $shipment = Shipment::load($shipment_id);
        $add_shipment = Shipment::create([
          'type' => 'default',
          'order_id' => $order->id(),
          'title' => $shipment->label(),
          'state' => 'ready',
        ]);

        $shipment_profile = $shipment->getShippingProfile();

        // Loop through order items and add them to shipment.
        foreach ($order->getItems() as $order_item) {
          $quantity = $order_item->getQuantity();
          $purchased_entity = $order_item->getPurchasedEntity();

          if ($purchased_entity->get('weight')->isEmpty()) {
            $weight = new Weight(1, WeightUnit::GRAM);
          }
          else {
            $weight_item = $purchased_entity->get('weight')->first();
            $weight = $weight_item->toMeasurement();
          }

          $shipment_item = new ShipmentItem([
            'order_item_id' => $order_item->id(),
            'title' => $purchased_entity->label(),
            'quantity' => $quantity,
            'weight' => $weight->multiply($quantity),
            'declared_value' => $order_item->getTotalPrice(),
          ]);
          $add_shipment->addItem($shipment_item);
        }

        /*// Loop through shipment items of the cloned order and add them to new order shipment
        $shipment_items = $shipment->getItems();

        foreach ($shipment_items as $shipment_item) {
        $shipment_item = new \Drupal\commerce_shipping\ShipmentItem([
        'order_item_id' => $shipment_item->getOrderItemId(),
        'title' => $shipment_item->getTitle(),
        'quantity' => $shipment_item->getQuantity(),
        'weight' => $shipment_item->getWeight(),
        'declared_value' => $shipment_item->getDeclaredValue(),
        ]);

        $add_shipment->addItem($shipment_item);
        }*/
        $shipping_method_storage = \Drupal::entityTypeManager()->getStorage('commerce_shipping_method');
        $shipping_methods = $shipping_method_storage->loadMultipleForShipment($add_shipment);
        $add_shipment->setShippingMethod(reset($shipping_methods));

        $add_shipment->setAmount($shipment->getAmount());
        $add_shipment->save();

        $order->set('shipments', [$add_shipment]);
        $order->save();

        $add_shipment->setShippingProfile($shipment_profile);
        $add_shipment->setShippingService($shipment->getShippingService());
        $add_shipment->setShippingMethod($shipment->getShippingMethod());
        $add_shipment->save();
        $order->set('shipments', $add_shipment);
        $order->save();

        return $order->id();

      }
    }
  }

}
