<?php

namespace Drupal\samhsa_pep_order_states_workflow\EventSubscriber;

use Drupal\samhsa_pep_order_states_workflow\WorkflowHelperInterface;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Component\Render\FormattableMarkup;

use Drupal\commerce_stock\EventSubscriber\OrderEventSubscriber;

/**
 * Event subscriber to handle revisions on workflow-enabled entities.
 */
class WorkflowTransitionEventSubscriber implements EventSubscriberInterface {

  /**
   * The workflow helper.
   *
   * @var \Drupal\samhsa_pep_order_states_workflow\WorkflowHelperInterface
   */
  protected $workflowHelper;

  /**
   * Constructs a new WorkflowTransitionEventSubscriber object.
   *
   * @param \Drupal\samhsa_pep_order_states_workflow\WorkflowHelperInterface $workflowHelper
   *   The workflow helper.
   */
  public function __construct(WorkflowHelperInterface $workflowHelper) {
    $this->workflowHelper = $workflowHelper;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      'state_machine.pre_transition' => 'handleAction',
      'commerce_order.placebulk.post_transition' => 'handlePlaceBulkOrderAction',
      'commerce_order.place.post_transition' => ['handlePlaceRegularOrderAction', -300],
    ];
  }

  /**
   * Handle action based on the workflow.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The state change event.
   */
  public function handleAction(WorkflowTransitionEvent $event) {
    $entity = $event->getEntity();
    $fields = $this->workflowHelper->getEntityStateField($entity);
    if ($entity instanceof OrderInterface) {
      $state = $entity->getState()->getValue()['value'];
      switch ($state) {
        case 'canceled':
          // Send email notification.
          // Fetch owner email.
          $to = $entity->getEmail();
          $ordernumber = $entity->id();
          $subject = "Order Cancellation.";

          $message = new FormattableMarkup("Order # %ordernumber<br /><br />
                        Dear customer,<br /><br />
                        Your order has been cancelled. If you have any questions or did not request a cancellation, please use the Order # provided above when contacting us.<br /><br />
                        Regards,<br /><br />
                        SAMHSA Fulfillment Team<br /><br />
                        If you have questions or comments regarding your order, please send an email to 
                        <a href = 'mailto:order@samhsa.hhs.gov'>order@samhsa.hhs.gov</a> with your order number. 
                        For all other questions or comments, please contact <a href = 'mailto:SAMHSAInfo@SAMHSA.hhs.gov'>SAMHSAInfo@SAMHSA.hhs.gov</a>.",
                ['%ordernumber' => $ordernumber]);
          \Drupal::messenger()->addStatus(t("Order Cancelled"));
          if (function_exists('send_mail')) {
            send_mail($entity, 'samhsa_pep', 'order_state', $subject, $message, $ordernumber, $to, FALSE);
          }
          break;

        case 'completed':
          // Send email notification.
          // Fetch owner email.
          $to = $entity->getEmail();
          $ordernumber = $entity->id();
          $subject = "Order Shipped.";

          $message = new FormattableMarkup("Order # %ordernumber<br /><br />
                        Dear customer,<br /><br />
                        Your order has been shipped. You should receive the materials in 10-12 days<br />                       
                        If you have any questions about your order, please use the Order # provided above when contacting us.<br />                       
                        Note: If you ordered products over the Max Limit, only the authorized quantity has been shipped.<br /><br />  
                        Regards,<br /> <br />
                        SAMHSA Fulfillment Team<br /><br />
                        If you have questions or comments regarding your order, please send an email to 
                        <a href = 'mailto:order@samhsa.hhs.gov'>order@samhsa.hhs.gov</a> with your order number. 
                        For all other questions or comments, please contact <a href = 'mailto:SAMHSAInfo@SAMHSA.hhs.gov'>SAMHSAInfo@SAMHSA.hhs.gov</a>.",
                ['%ordernumber' => $ordernumber]);

          \Drupal::messenger()->addStatus("Order Shipped");
          if (function_exists('send_mail')) {
            send_mail($entity, 'samhsa_pep', 'order_state', $subject, $message, $ordernumber, $to, FALSE);
          }
          break;

        case 'onhold':
          break;
      }
    }
  }

  /**
   * Handle action based on the workflow.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The state change event.
   */
  public function handlePlaceBulkOrderAction(WorkflowTransitionEvent $event) {
    $order = $event->getEntity();

    // Place bulk order and call stock transaction to decrease stock.
    $stockServiceManager = \Drupal::service('commerce_stock.service_manager');
    $stockEventTypeManager = \Drupal::service('plugin.manager.commerce_stock_event_type');
    $stockEventsManager = \Drupal::service('plugin.manager.stock_events');
    $entityTypeManager = \Drupal::entityTypeManager();
    $oes = new OrderEventSubscriber($stockServiceManager, $stockEventTypeManager, $stockEventsManager, $entityTypeManager);
    $oes->onOrderPlace($event);

    // Send email notification.
    // Fetch owner email.
    $to = $order->getEmail();
    $ordernumber = $order->id();
    $subject = "Order Submitted.";

    $message = new FormattableMarkup("Order # %ordernumber<br /><br />
                        Dear customer,<br /><br />
                        Thank you for your order. Since the quantity ordered exceeds the maximum limit, your order will require authorization. You will receive an email shortly with more details.<br /><br />                      
                        If you have any questions about your order, please use the Order # provided above when contacting us.<br /><br />                     
                                    
                        Regards,<br /> <br />  
                        SAMHSA Fulfillment Team<br /><br /> 
                        If you have questions or comments regarding your order, please send an email to 
                        <a href = 'mailto:order@samhsa.hhs.gov'>order@samhsa.hhs.gov</a> with your order number. 
                        For all other questions or comments, please contact <a href = 'mailto:SAMHSAInfo@SAMHSA.hhs.gov'>SAMHSAInfo@SAMHSA.hhs.gov</a>.",
          ['%ordernumber' => $ordernumber]);

    \Drupal::messenger()->addStatus("Order Submitted");
    if (function_exists('send_mail')) {
      send_mail($order, 'samhsa_pep', 'order_state', $subject, $message, $ordernumber, $to, TRUE);
    }
  }

  /**
   * Handle action based on the workflow.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The state change event.
   */
  public function handlePlaceRegularOrderAction(WorkflowTransitionEvent $event) {
    $entity = $event->getEntity();
    // Send email notification.
    // Fetch owner email.
    $to = $entity->getEmail();
    $ordernumber = $entity->id();
    $subject = "Order Submitted.";

    $message = new FormattableMarkup("Order # %ordernumber<br /><br />
                        Dear customer,<br /><br />
                        Thank you for your order. You should receive the materials in 3-4 weeks.<br /><br />                      
                        If you have any questions about your order, please use the Order # provided above when contacting us.<br /><br />                       
                                    
                        Regards,<br /> 
                        SAMHSA Fulfillment Team<br /><br />
                        If you have questions or comments regarding your order, please send an email to 
                        <a href = 'mailto:order@samhsa.hhs.gov'>order@samhsa.hhs.gov</a> with your order number. 
                        For all other questions or comments, please contact <a href = 'mailto:SAMHSAInfo@SAMHSA.hhs.gov'>SAMHSAInfo@SAMHSA.hhs.gov</a>.",
          ['%ordernumber' => $ordernumber]);

    \Drupal::messenger()->addStatus("Order Submitted");
    if (function_exists('send_mail')) {
      send_mail($entity, 'samhsa_pep', 'order_state', $subject, $message, $ordernumber, $to, TRUE);
    }
  }

}
