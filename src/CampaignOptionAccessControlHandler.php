<?php

namespace Drupal\commerce_recruiting;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the campaign option entity.
 *
 * @see \Drupal\commerce_recruiting\Entity\CampaignOption.
 */
class CampaignOptionAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\commerce_recruiting\Entity\CampaignOptionInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished campaign option entities');
        }

        return AccessResult::allowedIfHasPermission($account, 'view published campaign option entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit campaign option entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete campaign option entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add campaign option entities');
  }

}
