<?php

/**
 * @file
 * Contains \Drupal\migrate_files_and_images\FilesCrossReferenceAccessControlHandler.
 */

namespace Drupal\migrate_files_and_images;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Files cross reference entity.
 *
 * @see \Drupal\migrate_files_and_images\Entity\FilesCrossReference.
 */
class FilesCrossReferenceAccessControlHandler extends EntityAccessControlHandler {
  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\migrate_files_and_images\FilesCrossReferenceInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished files cross reference entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published files cross reference entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit files cross reference entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete files cross reference entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add files cross reference entities');
  }

}
