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
class RecruitingConfigAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\commerce_recruitment\Entity\RecruitingInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished recruiting config entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published recruiting config entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit recruiting config entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete recruiting config entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add recruiting config entities');
  }

}
