<?php

namespace Drupal\samhsa_pep_utility\Plugin\Action;

use Drupal;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Action to set order status to Pending.
 *
 * @Action(
 *   id = "samhsa_pep_utility_pending",
 *   label = @Translation("Move to Pending"),
 *   type = "commerce_order",
 *   confirm = TRUE,
 *   requirements = {
 *     "_permission" = "move order to pending",
 *     "_custom_access" = TRUE,
 *   },
 * )
 */
class SetOrderToPending extends ViewsBulkOperationsActionBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    // Do some processing..
    if ($entity->hasField('state')) {
      $entity->set('state', 'pending');
      $entity->save();
      $logcomments = '';
      if ($entity->hasField('field_log')) {
        $logcomments = $entity->field_log->value;
      }
      $log_storage = Drupal::entityTypeManager()->getStorage('commerce_log');
      $log = $log_storage->generate($entity, 'commerce_order_state_updated', ['message' => "Pending: " . $logcomments])
        ->save();
    }
    // Don't return anything for a default completion message, otherwise return translatable markup.
    return $this->t('Order status changed to Pending');
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
