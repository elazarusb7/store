<?php

namespace Drupal\samhsa_term_elevation;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the SAHMSA Term Elevation entity.
 *
 * @see \Drupal\samhsa_term_elevation\Entity\SamhsaTermElevation.
 */
class SamhsaTermElevationAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\samhsa_term_elevation\Entity\SamhsaTermElevationInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished sahmsa term elevation entities');
        }

        return AccessResult::allowedIfHasPermission($account, 'view published sahmsa term elevation entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit sahmsa term elevation entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete sahmsa term elevation entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add sahmsa term elevation entities');
  }

}
