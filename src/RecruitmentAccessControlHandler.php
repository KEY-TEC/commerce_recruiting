<?php

namespace Drupal\commerce_recruiting;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the recruitment entity.
 *
 * @see \Drupal\commerce_recruiting\Entity\Recruitment.
 */
class RecruitmentAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\commerce_recruiting\Entity\RecruitmentInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished recruitment entity entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published recruitment entity entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit recruitment entity entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete recruitment entity entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add recruitment entity entities');
  }

}
