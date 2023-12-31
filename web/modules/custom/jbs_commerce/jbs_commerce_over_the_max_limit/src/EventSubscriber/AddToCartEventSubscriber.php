<?php

namespace Drupal\jbs_commerce_over_the_max_limit\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\commerce_cart\Event\CartEvents;
use Drupal\commerce_cart\Event\CartEntityAddEvent;
use Drupal\commerce_cart\Event\CartOrderItemUpdateEvent;
use Drupal\commerce_order\Event\OrderEvents;
use Drupal\commerce_order\Event\OrderItemEvent;
use Drupal\commerce_order\AvailabilityResult;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Class AddToCartEventSubscriber.
 */
class AddToCartEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new CartEventSubscriber object.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation.
   */
  public function __construct(MessengerInterface $messenger, TranslationInterface $string_translation) {
    $this->messenger = $messenger;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      CartEvents::CART_ENTITY_ADD => 'displayOverTheMaxLimitMessage',
      CartEvents::CART_ORDER_ITEM_UPDATE => 'displayOverTheMaxLimitMessageCartUpdate',
      OrderEvents::ORDER_ITEM_UPDATE => 'displayOverTheMaxLimitMessageOrderUpdate',
    ];
  }

  /**
   * Displays 'over the limit' cart message.
   *
   * Event fires when a customer adds a product to their cart.
   *
   * @param \Drupal\commerce_cart\Event\CartEntityAddEvent $event
   *   The add to cart event.
   */
  public function displayOverTheMaxLimitMessage(CartEntityAddEvent $event) {
    /** @var Drupal\commerce_order\Entity\OrderItem $item */
    $item = $event->getOrderItem();
    $this->displayMessage($item, get_class($event));
  }

  /**
   * Displays 'over the limit' cart message.
   *
   * Event fires when a customer edits a product to their cart.
   *
   * @param \Drupal\commerce_cart\Event\CartOrderItemUpdateEvent $event
   *   The add to cart event.
   */
  public function displayOverTheMaxLimitMessageCartUpdate(CartOrderItemUpdateEvent $event) {
    /** @var Drupal\commerce_order\Entity\OrderItem $item */
    $item = $event->getOrderItem();
    $this->displayMessage($item, get_class($event));
  }

  /**
   * Displays 'over the limit' cart message.
   *
   * Event fires when an admit edits an order or when
   * a customer adds a product to their cart, after
   * the CartEvents::CART_ENTITY_ADD event.
   *
   * @param \Drupal\commerce_order\Event\OrderItemEvent $event
   *   The add to cart event.
   */
  public function displayOverTheMaxLimitMessageOrderUpdate(OrderItemEvent $event) {
    /** @var Drupal\commerce_order\Entity\OrderItem $item */
    $item = $event->getOrderItem();
    $this->displayMessage($item, get_class($event));
  }

  /**
   * Computes if order quantity is over max limit for product,
   * displays message to user if over.
   *
   * @param Drupal\commerce_order\Entity\OrderItem $item
   */
  private function displayMessage($item, $event_type)
  {
    // OCWT-3522 - ignore order limit with proper perms
    if (\Drupal::currentUser()->hasPermission('bypass max number products order')) {
      return;
    }

    $type_expanded = explode('\\', $event_type);
    $type_class = array_pop($type_expanded);
    $title = $item->label();
    $total_qty_in_cart = $item->getQuantity();
    $product = $item->getPurchasedEntity()->getProduct();
    $maxlimit_field_name = \Drupal::config('jbs_commerce_over_the_max_limit.settings')
      ->get('maxlimit_element');
    // field_qty_max_order.
    if (empty($maxlimit_field_name)) {
      return;
    }
    $maxlimit_field = $product->get($maxlimit_field_name);
    if (!isset($maxlimit_field)) {
      return;
    }
    $maxlimit_value = $maxlimit_field->getValue();
    if (!isset($maxlimit_value[0]['value'])) {
      return;
    }
    $max = $maxlimit_value[0]['value'];
    if ($total_qty_in_cart > $max) {
      if ($type_class != 'OrderItemEvent') {
        $msg = "The max order limit for this product is \"$max\".";
        $this->messenger->addMessage(t($msg));
        return AvailabilityResult::unavailable($msg);
      }
    }
  }

}
