<?php

namespace Drupal\commerce_recruiting;

use Drupal\commerce_recruiting\Entity\CampaignOptionInterface;
use Drupal\user\Entity\User;

/**
 * Stores the active recruiting session.
 *
 * @see \Drupal\commerce_recruiting\RecruitingSessionInterface
 */
interface RecruitingSessionInterface {
  // The recruiting session types.
  const RECRUITER = 'recruiter';
  const CAMPAIGN_OPTION = 'campaign_option';

  /**
   * Gets the recruiter.
   *
   * @return \Drupal\user\Entity\User
   *   The recruiter.
   */
  public function getRecruiter();

  /**
   * Gets the recruiting campaign option.
   *
   * @return \Drupal\commerce_recruiting\Entity\CampaignOption
   *   The recruiting campaign option.
   */
  public function getCampaignOption();

  /**
   * Sets the recruiter.
   *
   * @param \Drupal\user\Entity\User $recruiter
   *   The recruiter.
   */
  public function setRecruiter(User $recruiter);

  /**
   * Sets the recruiting campaign option.
   *
   * @param \Drupal\commerce_recruiting\Entity\CampaignOptionInterface $recruiting
   *   The recruiting campaign option.
   */
  public function setRecruitingCampaignOption(CampaignOptionInterface $recruiting);

}
