<?php

namespace Drupal\commerce_recruiting\Guard;

use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\state_machine\Guard\GuardInterface;
use Drupal\state_machine\Plugin\Workflow\WorkflowTransition;
use Drupal\state_machine\Plugin\Workflow\WorkflowInterface;
use Drupal\state_machine\WorkflowManagerInterface;

/**
 * Access controller for the Recruiting entity entity.
 *
 * @see \Drupal\commerce_recruiting\Entity\Recruiting.
 */
class RecrutingGuard implements GuardInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The workflow manager.
   *
   * @var \Drupal\state_machine\WorkflowManagerInterface
   */
  protected $workflowManager;

  /**
   * Constructs a new PublicationGuard object.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user..
   * @param \Drupal\state_machine\WorkflowManagerInterface $workflow_manager
   *   The workflow manager.
   */
  public function __construct(AccountProxyInterface $current_user, WorkflowManagerInterface $workflow_manager) {
    $this->currentUser = $current_user;
    $this->workflowManager = $workflow_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function allowed(WorkflowTransition $transition, WorkflowInterface $workflow, EntityInterface $entity) {
    /** @var \Drupal\commerce_recruiting\Entity\Recruiting $recruiting */
    $recruiting = $entity;
    if ($transition->getToState()->getId() == 'accepted') {
      if ($recruiting->getOrder() != NULL && $recruiting->getOrder()->getState()->getId() != 'completed'
        && $recruiting->getProduct() instanceof ProductVariation
      ) {
        return FALSE;
      }
    }

    return TRUE;
  }

}
