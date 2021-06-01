<?php

/**
 * @file
 * Contains \Drupal\samhsa_pep_migrate_custom\UnconvertedLinksAccessControlHandler.
 */

namespace Drupal\samhsa_pep_migrate_custom;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Unconverted links entity.
 *
 * @see \Drupal\samhsa_pep_migrate_custom\Entity\UnconvertedLinks.
 */
class UnconvertedLinksAccessControlHandler extends EntityAccessControlHandler {
  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\samhsa_pep_migrate_custom\UnconvertedLinksInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished unconverted links entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published unconverted links entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit unconverted links entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete unconverted links entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add unconverted links entities');
  }

}
