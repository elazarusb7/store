<?php

namespace Drupal\jbs_commerce_over_the_max_limit\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\commerce_order\Event\OrderEvents;
use Drupal\commerce_order\Event\OrderEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Class OrderEventSubscriber.
 */
class OrderEventSubscriber implements EventSubscriberInterface {

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
   * This class is a placeholder for future implementation of the Event Subscriber
   * for tracking changes in the Order.
   */

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      OrderEvents::ORDER_UPDATE => 'orderTypeUpdateIfBulk', 300,
    ];
  }

  /**
   * Displays an add to cart message.
   *
   * @param \Drupal\commerce_order\Event\OrderItemEvent $event
   *   The add to cart event.
   */
  public function orderTypeUpdateIfBulk(OrderEvent $event) {
    /* when order is completed
     * check if at least one item is over the max limit
     * set order type to bulk
     */
    /*$order = $event->getOrder();

    if(isset($order) && $order->get('checkout_step')->value == 'complete') {
    $isbulk = \Drupal::service('samhsa_pep_utility.pep_utility_functions')->isOrderBulk($order);
    $order_type = $isbulk ? 'samhsa_publication_ob' : 'default';
    $order->get('type')->__set('target_id', $order_type);
    $order->save();
    }
    return;*/
  }

}
