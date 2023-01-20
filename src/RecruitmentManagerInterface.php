<?php

namespace Drupal\commerce_recruiting;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_recruiting\Entity\CampaignInterface;
use Drupal\commerce_recruiting\Entity\CampaignOptionInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Interface for recruitment manager services.
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
   * @param \Drupal\commerce_recruiting\Entity\CampaignOptionInterface $option
   *   The campaign option.
   * @param \Drupal\commerce_price\Price $bonus
   *   The bonus.
   *
   * @return \Drupal\commerce_recruiting\Entity\Recruitment
   *   The recruitment entity
   */
  public function createRecruitment(OrderItemInterface $order_item, AccountInterface $recruiter, AccountInterface $recruited, CampaignOptionInterface $option, Price $bonus);

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
   * Try to load recruitments via an order.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The commerce order.
   *
   * @return \Drupal\commerce_recruiting\Entity\RecruitmentInterface[]|null
   *   Loaded recruitments from order.
   */
  public function getRecruitmentsByOrder(OrderInterface $order);

  /**
   * Checks the order for matching products from RecruitmentSession.
   *
   * Will check the recruiting session against the given order for matching
   * products in campaign options and calculates a provisionally bonus.
   * The bonus is re-evaluated on checkout complete.
   *
   * Since the session only exists in short-term, the information returned here
   * should be saved in an entity, using the recruiting info field, to make it
   * persist. This is done in RecruitmentCheckoutSubscriber::onOrderItemAdd().
   * With this recruitments can be created anytime when the user decides to
   * complete the checkout later after starting a new session.
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
   * Calculates the sum of all recruitment bonus of a user.
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
