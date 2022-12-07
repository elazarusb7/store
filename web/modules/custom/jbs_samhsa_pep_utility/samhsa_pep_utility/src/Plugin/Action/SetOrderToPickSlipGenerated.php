<?php

namespace Drupal\samhsa_pep_utility\Plugin\Action;

use Drupal;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Exception;

/**
 * Action to set order status to Pick Slip Generated.
 *
 * @Action(
 *   id = "samhsa_pep_utility_pick_slip_generated",
 *   label = @Translation("Generate Pick Slip"),
 *   type = "commerce_order",
 *   confirm = TRUE,
 *   requirements = {
 *     "_permission" = "generate pick slips",
 *     "_custom_access" = TRUE,
 *   },
 * )
 */
class SetOrderToPickSlipGenerated extends ViewsBulkOperationsActionBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function execute2($entity = NULL) {
    // Do some processing..
    if ($entity->hasField('state')) {
      $entity->set('state', 'pick_slips_generated');
      $entity->save();
      $logcomments = '';
      if ($entity->hasField('field_log')) {
        $logcomments = $entity->field_log->value;
      }
      $log_storage = Drupal::entityTypeManager()->getStorage('commerce_log');
      $log = $log_storage->generate($entity, 'commerce_order_state_updated', ['message' => "Pick Slip Generated: " . $logcomments])
        ->save();
    }
    // Don't return anything for a default completion message, otherwise return translatable markup.
    return $this->t('Order status changed to Pick Slip Generated');
  }

  /**
   * @param array $entities
   */
  public function executeMultiple(array $entities) {
    // Holds the IDs for our orders.
    $order_ids = [];

    // Get order ids of selected orders.
    foreach ($entities as $order) {
      $order_ids[] = $order->id();
    }
    // Print the invoice of selected orders.
    try {
      Drupal::service('samhsa_pep_pdf_printing.generate_pdf')
        ->invoice($order_ids);
      foreach ($entities as $entity) {
        if ($entity->hasField('state')) {
          $entity->set('state', 'pick_slips_generated');
          $entity->save();
          $logcomments = '';
          if ($entity->hasField('field_log')) {
            $logcomments = $entity->field_log->value;
          }
          $log_storage = Drupal::entityTypeManager()
            ->getStorage('commerce_log');
          $log = $log_storage->generate($entity, 'commerce_order_state_updated', ['message' => "Pick Slip Generated: " . $logcomments])
            ->save();
        }
      }

    } catch (Exception $e) {
      echo 'Caught exception: ', $e->getMessage(), "\n";
    }

  }

  /**
   * Executes the plugin.
   */
  public function execute($entity = NULL) {
    $this->executeMultiple([$entity]);
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
