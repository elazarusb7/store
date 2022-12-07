<?php

namespace Drupal\samhsa_pep_utility\Plugin\Action;

use Drupal\user\Entity\User;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Action to set order status to Completed.
 *
 * @Action(
 *   id = "samhsa_pep_utility_cancel_order",
 *   label = @Translation("Cancel Order"),
 *   type = "commerce_order",
 *   confirm = TRUE,
 *   requirements = {
 *     "_permission" = "calcel order",
 *     "_custom_access" = TRUE,
 *   },
 * )
 */
class SetOrderToCancel extends ViewsBulkOperationsActionBase implements PluginFormInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $selectedItems = array_map(function ($listItem) {
        return reset($listItem);
    }, $this->context['list']);

    $tempstore = \Drupal::service('tempstore.private')->get('samhsa_pep_utility');

    $tempstore->set('list', $selectedItems);

    if (is_array($selectedItems) && count($selectedItems) > 0) {
      foreach ($selectedItems as $order_id) {
        $form['reason_for_change_' . $order_id] = [
          '#title' => t('Reason for cancellation for Order # ' . $order_id),
          '#type' => 'textfield',
          '#default_value' => '',
          '#required' => TRUE,
        ];
      }
    }
    else {
      $form['reason_for_change'] = [
        '#title' => t('Reason for cancellation for all selected orders'),
        '#type' => 'textfield',
        '#default_value' => '',
        '#required' => TRUE,
        '#description' => 'If you checked "Select / deselect all results in this view" to cancel multiple orders, only one input will be provided to enter an reason for the cancellation.  If you would like to provide a unique reason for each cancellation, use the "SELECT ALL" option on each page or select multiple orders individually.',
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // $tempstore = \Drupal::service('user.private_tempstore')->get('samhsa_pep_utility');
    $tempstore = \Drupal::service('tempstore.private')->get('samhsa_pep_utility');

    $selectedItems = $tempstore->get('list');
    $reasons = [];
    foreach ($selectedItems as $key => $id) {
      if ($form_state->getValue('reason_for_change_' . $id)) {
        $reason_for_change = $form_state->getValue('reason_for_change_' . $id);
        $reasons[$id] = $reason_for_change;
      }
    }
    $tempstore->set('reasons', $reasons);

    if ($form_state->getValue('reason_for_change')) {
      $reason_for_change = $form_state->getValue('reason_for_change');
      $tempstore->set('reason', $reason_for_change);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    // Do some processing..
    if ($entity->hasField('state')) {
      $entity->set('state', 'canceled');
      // User who canceled the order.
      $user = User::load(\Drupal::currentUser()->id());
      $username = $user->getAccountName();
      // $tempstore = \Drupal::service('user.private_tempstore')->get('samhsa_pep_utility');
      $tempstore = \Drupal::service('tempstore.private')->get('samhsa_pep_utility');
      $reason = $tempstore->get('reason');
      $reasons = $tempstore->get('reasons');
      if (is_array($reasons) && count($reasons) > 0) {
        $logcomments = $reasons[$entity->id()];
      }
      else {
        if (!empty($reason)) {
          $logcomments = $reason;
        }
      }
      $entity->set('field_log', $logcomments . " (" . $username . ")");
      $entity->save();

      $log_storage = \Drupal::entityTypeManager()->getStorage('commerce_log');
      $log = $log_storage->generate($entity, 'commerce_order_state_updated', ['message' => "Order Cancelled: " . $logcomments])->save();
    }
    // Don't return anything for a default completion message, otherwise return translatable markup.
    return $this->t('Order status changed to Cancelled');
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    if ($object->getEntityType() === 'commerce_order') {
      $access = $object->access('update', $account, TRUE)
        ->andIf($object->status->access('edit', $account, TRUE));
      return $return_as_object ? $access : $access->isAllowed();
    }

    // Other entity types may have different
    // access methods and properties.
    return TRUE;
  }

}
