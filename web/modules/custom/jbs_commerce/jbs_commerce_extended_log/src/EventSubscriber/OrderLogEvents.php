<?php

namespace Drupal\jbs_commerce_extended_log\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class OrderLogEvents.
 */
class OrderLogEvents implements EventSubscriberInterface {

  /**
   * Constructs a new OrderLogEvents object.
   */
  public function __construct() {

  }

  /**
   * Trackis changes in the Order.
   */

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [
//      'commerce_order.place.post_transition' => 'onOrderPlace',
      'commerce_order.placebulk.post_transition' => 'onBulkOrderPlace',
      'commerce_order.approve.post_transition' => 'onOrderApproved',
      'commerce_order.process.post_transition' => 'onOrderPickSlipsGenerated',
      'commerce_order.complete.post_transition' => 'onOrderComplete',
      'commerce_order.cancel.post_transition' => 'onOrderCancel',
      'commerce_order.backtopending.post_transition' => 'onOrderBackToPending',
      ];
    return $events;
  }

  /**
   * This method is called when the event "place" is dispatched.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The dispatched event.
   */
  public function onOrderPlace(Event $event) {
    // Placeholder for eventual implementation of "place" event.
  }

  /**
   * This method is called when the event "place" is dispatched.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The dispatched event.
   */
  public function onBulkOrderPlace(Event $event) {
    $change = new \stdClass();
    $change->logMessage = 'Bulk Order placed';
    $change->fieldName = 'order';
    $change->oldValue = NULL;
    $change->newValue = NULL;
    $change->order = $event->getEntity();
    jbs_commerce_extended_log_log_the_changes([$change]);
  }

  /**
   * This method is called when the event "place" is dispatched.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The dispatched event.
   */
  public function onOrderApproved(Event $event) {
    $change = new \stdClass();
    $change->logMessage = 'Order approved';
    $change->fieldName = 'order';
    $change->oldValue = NULL;
    $change->newValue = NULL;
    $change->order = $event->getEntity();
    jbs_commerce_extended_log_log_the_changes([$change]);
  }

  /**
   * This method is called when the event "process" is dispatched.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The dispatched event.
   */
  public function onOrderPickSlipsGenerated(Event $event) {
    $change = new \stdClass();
    $change->logMessage = 'Pick Slips Generated';
    $change->fieldName = 'order';
    $change->oldValue = NULL;
    $change->newValue = NULL;
    $change->order = $event->getEntity();
    jbs_commerce_extended_log_log_the_changes([$change]);
  }

  /**
   * This method is called when the event "complete" is dispatched.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The dispatched event.
   */
  public function onOrderComplete(Event $event) {
    $change = new \stdClass();
    $change->logMessage = 'The order was completed';
    $change->fieldName = 'order';
    $change->oldValue = NULL;
    $change->newValue = NULL;
    $change->order = $event->getEntity();
    jbs_commerce_extended_log_log_the_changes([$change]);
  }

  /**
   * This method is called when the event "cancel" is dispatched.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The dispatched event.
   */
  public function onOrderCancel(Event $event) {
    $change = new \stdClass();
    $change->logMessage = 'The order was canceled';
    $change->fieldName = 'order';
    $change->oldValue = NULL;
    $change->newValue = NULL;
    $change->order = $event->getEntity();
    jbs_commerce_extended_log_log_the_changes([$change]);
  }

  /**
   * This method is called when the event "backtopending" is dispatched.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The dispatched event.
   */
  public function onOrderBackToPending(Event $event) {
    $change = new \stdClass();
    $change->logMessage = 'The order is back to pending';
    $change->fieldName = 'order';
    $change->oldValue = NULL;
    $change->newValue = NULL;
    $change->order = $event->getEntity();
    jbs_commerce_extended_log_log_the_changes([$change]);
  }

}
