<?php

namespace Drupal\commerce_recruiting;

use Drupal\commerce_recruiting\Entity\CampaignOptionInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Stores the active recruitment session.
 *
 * @see \Drupal\commerce_recruiting\RecruitmentSessionInterface
 */
interface RecruitmentSessionInterface {
  // The recruitment session types.
  const RECRUITER = 'recruiter';
  const CAMPAIGN_OPTION = 'campaign_option';

  /**
   * Gets the recruiter.
   *
   * @return \Drupal\Core\Session\AccountInterface
   *   The recruiter.
   */
  public function getRecruiter();

  /**
   * Gets the campaign option.
   *
   * @return \Drupal\commerce_recruiting\Entity\CampaignOption
   *   The campaign option.
   */
  public function getCampaignOption();

  /**
   * Sets the recruiter.
   *
   * @param \Drupal\Core\Session\AccountInterface $recruiter
   *   The recruiter.
   */
  public function setRecruiter(AccountInterface $recruiter);

  /**
   * Sets the campaign option.
   *
   * @param \Drupal\commerce_recruiting\Entity\CampaignOptionInterface $campaign_option
   *   The campaign option.
   */
  public function setCampaignOption(CampaignOptionInterface $campaign_option);

}
