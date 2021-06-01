<?php

namespace Drupal\jbs_commerce_product_recommendation\EventSubscriber;

use Drupal\commerce_cart\Event\CartEntityAddEvent;
use Drupal\commerce_cart\Event\CartOrderItemRemoveEvent;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\commerce_cart\Event\CartEvents;

/**
 * Class CommerceEventSubscriber
 *
 * @package Drupal\jbs_commerce_product_recommendation\EventSubscriber
 */
class CommerceEventSubscriber implements EventSubscriberInterface {

  private $eventNames = [
    CartEvents::CART_ENTITY_ADD => 'cart_entity_added',
    CartEvents::CART_ORDER_ITEM_REMOVE => 'cart_entity_removed',
    'commerce_order.place.post_transition' => 'order_placed',
  ];

  public function __construct() {}

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() {
    return [
      CartEvents::CART_ENTITY_ADD => 'onCartEntityAddEvent',
      CartEvents::CART_ORDER_ITEM_REMOVE => 'onCartOrderItemRemoveEvent',
      'commerce_order.place.post_transition' => 'onOrderPlacedEvent',
    ];
  }

  /**
   * Logs add-to-cart event to the database.
   *
   * @param \Drupal\commerce_cart\Event\CartEntityAddEvent $event
   *  The add to cart event.
   *
   * @throws \Exception
   */
  public function onCartEntityAddEvent(CartEntityAddEvent $event) {
    $item = $event->getEntity();
    $this->addEventToDatabase($item->get('product_id')->getValue()[0]['target_id'], $this->eventNames[CartEvents::CART_ENTITY_ADD]);
  }

  /**
   * Logs order deletion from cart event to the database.
   *
   * @param \Drupal\commerce_cart\Event\CartOrderItemRemoveEvent $event
   *
   * @throws \Exception
   */
  public function onCartOrderItemRemoveEvent(CartOrderItemRemoveEvent $event){
    $item = $event->getOrderItem();
    $this->addEventToDatabase($item->get('purchased_entity')->getValue()[0]['target_id'], $this->eventNames[CartEvents::CART_ORDER_ITEM_REMOVE]);
  }

  /**
   * Logs order placed event to the database.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *
   * @throws \Exception
   */
  public function onOrderPlacedEvent(WorkflowTransitionEvent $event) {
    $item = $event->getEntity()->getItems();
    foreach ($item as $o => $order) {
      $this->addEventToDatabase($order->get('purchased_entity')->getValue()[0]['target_id'], $this->eventNames['commerce_order.place.post_transition']);
    }
  }

  /**
   * addEventToDatabase() adds an event to the event table specified in the configurations
   *
   * @param $id
   * @param $eventName
   *
   * @throws \Exception
   */
  private function addEventToDatabase($id, $eventName) {
    \Drupal::database()->insert(\Drupal::config('jbs_commerce_product_recommendation.settings')->get('eventsTableName'))
      ->fields([
        'content_type' => 'commerce_product', // placeholder type
        'content_id' => $id, // event item id
        'timestamp' => time(),
        'session_id' => \Drupal\Component\Utility\Crypt::hashBase64(session_id()),
        'event' => $eventName, // type of event
      ])
      ->execute();
  }
}
