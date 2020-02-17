<?php

namespace Drupal\commerce_recruiting;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Recruiting entity entity.
 *
 * @see \Drupal\commerce_recruiting\Entity\Recruiting.
 */
class CampaignAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\commerce_recruiting\Entity\RecruitingInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished recruiting campaign entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published recruiting campaign entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit recruiting campaign entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete recruiting campaign entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add recruiting campaign entities');
  }

}
