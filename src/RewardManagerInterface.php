<?php

namespace Drupal\commerce_recruiting;

use Drupal\commerce_recruiting\Entity\CampaignInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Interface RewardManagerInterface.
 */
interface RewardManagerInterface {

  /**
   * Create a reward for given campaign.
   *
   * @param \Drupal\commerce_recruiting\Entity\CampaignInterface $campaign
   *   The campaign.
   * @param \Drupal\Core\Session\AccountInterface $recruiter
   *   The recruiter.
   *
   * @return \Drupal\commerce_recruiting\Entity\RewardInterface
   *   The created reward.
   */
  public function createReward(CampaignInterface $campaign, AccountInterface $recruiter);

  /**
   * Find rewards.
   *
   * @param \Drupal\Core\Session\AccountInterface $recruiter
   *   The recruiter
   *
   * @return \Drupal\commerce_recruiting\Entity\RewardInterface[]
   *   List of rewards.
   */
  public function findRewards(AccountInterface $recruiter);

}
