<?php

namespace Drupal\commerce_recruiting;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the reward entity.
 *
 * @see \Drupal\commerce_recruiting\Entity\Reward.
 */
class RewardAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\commerce_recruiting\Entity\RewardInterface $entity */

    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished reward entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published reward entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit reward entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete reward entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add reward entities');
  }

}
