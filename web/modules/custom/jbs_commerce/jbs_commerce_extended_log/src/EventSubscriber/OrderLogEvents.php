<?php

namespace Drupal\jbs_commerce_extended_log\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
// Use Symfony\Component\EventDispatcher\Event;.
use Drupal\state_machine\Event\WorkflowTransitionEvent;

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
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   */
  public function onOrderPlace(WorkflowTransitionEvent $event) {
    // Placeholder for eventual implementation of "place" event.
  }

  /**
   * This method is called when the event "place" is dispatched.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function onBulkOrderPlace(WorkflowTransitionEvent $event) {
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
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function onOrderApproved(WorkflowTransitionEvent $event) {
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
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function onOrderPickSlipsGenerated(WorkflowTransitionEvent $event) {
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
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function onOrderComplete(WorkflowTransitionEvent $event) {
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
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function onOrderCancel(WorkflowTransitionEvent $event) {
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
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function onOrderBackToPending(WorkflowTransitionEvent $event) {
    $change = new \stdClass();
    $change->logMessage = 'The order is back to pending';
    $change->fieldName = 'order';
    $change->oldValue = NULL;
    $change->newValue = NULL;
    $change->order = $event->getEntity();
    jbs_commerce_extended_log_log_the_changes([$change]);
  }

}
