<?php

namespace Drupal\samhsa_pep_shipping\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\commerce_order\Event\OrderEvents;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\commerce_order\Event\OrderEvent;

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
   * Constructs a new OrderEventSubscriber object.
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
      OrderEvents::ORDER_UPDATE => 'orderWeightUpdate',
    ];
  }

  /**
   * Update Order weight.
   *
   * @param \Drupal\commerce_order\Event\OrderEvent $event
   *   The order update event.
   */
  public function orderWeightUpdate(OrderEvent $event) {
    /* when order is completed
     * recalculate Order Weight and save/update into the database
     */
    $order = $event->getOrder();
    if (isset($order) && $order->get('checkout_step')->value == 'complete') {
      $weight = \Drupal::service('samhsa_pep_utility.pep_utility_functions')->recalculateWeight($order);
      $this->updateOrderWeightInDatabase($order, $weight);
    }
    return;
  }

  /**
   * UpdateOrderWeightInDatabase() updates order weight in the database.
   *
   * @param $order
   * @param $weight
   *
   * @throws \Exception
   */
  private function updateOrderWeightInDatabase($order, $weight) {
    $query = \Drupal::database()->select('commerce_order__field_order_weight', 't');
    $query->fields('t', ['entity_id']);
    $query->condition('entity_id', $order->id(), '=');
    $results = $query->orderBy('entity_id', 'ASC')->execute();
    $results->allowRowCount = TRUE;
    $unit = \Drupal::config('samhsa_pep_shipping.settings')
      ->get('unit', 'oz');
    // Get redirects if anything set for the given node.
    if ($results->rowCount() > 0) {
      // Update.
      \Drupal::database()->update('commerce_order__field_order_weight')
        ->condition('entity_id', $order->id(), '=')
        ->fields([
          'field_order_weight_number' => $weight,
          'field_order_weight_unit' => $unit,
        ])
        ->execute();
    }
    else {
      // Insert.
      $type = $order->get('type')->getValue()[0]['target_id'];
      $lang_code = \Drupal::languageManager()->getCurrentLanguage()->getId();

      \Drupal::database()->insert('commerce_order__field_order_weight')
        ->fields([
          'field_order_weight_number' => $weight,
          'field_order_weight_unit' => $unit,
          'entity_id' => $order->id(),
          'revision_id' => $order->id(),
          'bundle' => $type,
          'delta' => 0,
          'langcode' => $lang_code,
        ])
        ->execute();
    }

  }

}
