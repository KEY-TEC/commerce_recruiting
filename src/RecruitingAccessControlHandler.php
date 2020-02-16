<?php

namespace Drupal\commerce_recruitment;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Recruiting entity entity.
 *
 * @see \Drupal\commerce_recruitment\Entity\Recruiting.
 */
class RecruitingAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\commerce_recruitment\Entity\RecruitingInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished recruiting entity entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published recruiting entity entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit recruiting entity entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete recruiting entity entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add recruiting entity entities');
  }

}
