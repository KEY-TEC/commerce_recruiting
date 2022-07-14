<?php

namespace Drupal\commerce_recruiting;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_recruiting\Entity\CampaignInterface;
use Drupal\commerce_recruiting\Entity\CampaignOption;
use Drupal\commerce_recruiting\Entity\CampaignOptionInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Interface RecruitmentManagerInterface.
 */
interface RecruitmentManagerInterface {

  /**
   * Create a recruitment entity.
   *
   * @param \Drupal\commerce_order\Entity\OrderItemInterface $order_item
   *   The responding order item.
   * @param \Drupal\Core\Session\AccountInterface $recruiter
   *   The recruiter user.
   * @param \Drupal\Core\Session\AccountInterface $recruited
   *   The recruited user.
   * @param \Drupal\commerce_recruiting\Entity\CampaignOption $option
   *   The campaign option.
   * @param \Drupal\commerce_price\Price $bonus
   *   The bonus.
   *
   * @return \Drupal\commerce_recruiting\Entity\Recruitment
   *   The recruitment entity
   */
  public function createRecruitment(OrderItemInterface $order_item, AccountInterface $recruiter, AccountInterface $recruited, CampaignOption $option, Price $bonus);

  /**
   * Finds recruitments by given campaign and state.
   *
   * @param \Drupal\commerce_recruiting\Entity\CampaignInterface $campaign
   *   The campaign.
   * @param string $state
   *   The state of the recruitments.
   * @param \Drupal\Core\Session\AccountInterface $recruiter
   *   The recruiter.
   *
   * @return \Drupal\commerce_recruiting\Entity\RecruitmentInterface[]
   *   List of recruitment entities.
   */
  public function findRecruitmentsByCampaign(CampaignInterface $campaign, $state, AccountInterface $recruiter = NULL);

  /**
   * Finds recruitments by given campaign and state.
   *
   * @param \Drupal\commerce_recruiting\Entity\CampaignInterface $campaign
   *   The campaign.
   * @param string $state
   *   The state of the recruitments.
   * @param \Drupal\Core\Session\AccountInterface $recruiter
   *   The recruiter.
   *
   * @return \Drupal\commerce_recruiting\RecruitmentSummary
   *   The recruitment summary.
   */
  public function getRecruitmentSummaryByCampaign(CampaignInterface $campaign, $state, AccountInterface $recruiter = NULL);

  /**
   * Checks the order for matching products from RecruitmentSession.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order.
   *
   * @return array
   *   keys:
   *    0
   *      - order_item
   *      - commerce_recruitment_camp_option
   *    etc.
   */
  public function sessionMatch(OrderInterface $order);

  /**
   * Resolves the recruitment bonus for a single order item.
   *
   * @param \Drupal\commerce_recruiting\Entity\CampaignOptionInterface $option
   *   The applied campaign option.
   * @param \Drupal\commerce_order\Entity\OrderItemInterface $order_item
   *   The order item.
   *
   * @return \Drupal\commerce_price\Price
   *   The resolved recruitment bonus.
   */
  public function resolveRecruitmentBonus(CampaignOptionInterface $option, OrderItemInterface $order_item);

  /**
   * Calculates the sum of all recruitment bonus of an user.
   *
   * @param int $uid
   *   User ID.
   * @param bool $include_paid_out
   *   Set true to include already paid out recruitments.
   *
   * @return int
   *   The total bonus.
   */
  public function getTotalBonusPerUser($uid, $include_paid_out = FALSE);

  /**
   * Manages the recruitment state transition.
   *
   * @param string $state
   *   The state.
   */
  public function applyTransitions($state);

}
