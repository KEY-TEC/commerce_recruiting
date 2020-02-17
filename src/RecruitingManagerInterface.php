<?php

namespace Drupal\commerce_recruiting;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_recruiting\Entity\CampaignOption;
use Drupal\user\Entity\User;

/**
 * Interface RecruitingManagerInterface.
 */
interface RecruitingManagerInterface {

  /**
   * Calculates the sum of all recruiting bonus of an user.
   *
   * @param int $uid
   *   User ID.
   * @param bool $include_paid_out
   *   Set true to include already paid out recruiting bonus.
   * @param string $recruiting_type
   *   Filter by recruiting type. Leave empty to include all types.
   *
   * @return int
   *   The total bonus.
   */
  public function getTotalBonusPerUser($uid, $include_paid_out = FALSE, $recruiting_type = NULL);

  /**
   * Create recruiting entity.
   *
   *   The order item.
   *
   * @param \Drupal\commerce_order\Entity\OrderItemInterface $order_item
   *   The responding order item.
   * @param \Drupal\user\Entity\User $recruiter
   *   The recruiter user.
   * @param \Drupal\user\Entity\User $recruited
   *   The recruited user.
   * @param \Drupal\commerce_recruiting\Entity\CampaignOption $option
   *   The recruiting campaign option.
   * @param \Drupal\commerce_price\Price $bonus
   *   The bonus.
   *
   * @return \Drupal\commerce_recruiting\Entity\Recruiting
   *   The recruiting entity
   */
  public function createRecruiting(OrderItemInterface $order_item, User $recruiter, User $recruited, CampaignOption $option, Price $bonus);

  /**
   * Checks the order for recommend products in RecruitingSession.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order.
   *
   * @return array
   *   keys:
   *    0
   *      - order_item
   *      - commerce_recruiting_camp_option
   *    etc.
   */
  public function sessionMatch(OrderInterface $order);

  /**
   * Manages the recruiting state transition.
   *
   * @param string $state
   *   The state.
   */
  public function applyTransitions($state);

}
