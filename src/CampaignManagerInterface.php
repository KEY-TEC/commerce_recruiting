<?php

namespace Drupal\commerce_recruiting;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Interface CampaignManagerInterface.
 */
interface CampaignManagerInterface {

  /**
   * Returns campaigns.
   *
   * The method will try to find and campaigns
   * filtered by recruiter and/or product if given.
   *
   * @param \Drupal\Core\Session\AccountInterface|null $recruiter
   *   Optional filter configs by recruiter.
   * @param \Drupal\Core\Entity\EntityInterface|null $product
   *   Optional filter configs by product.
   *
   * @return \Drupal\commerce_recruiting\Entity\CampaignInterface[]
   *   The found recruiting campaign option.
   */
  public function findCampaigns(AccountInterface $recruiter = NULL, EntityInterface $product = NULL);

  /**
   * Returns recruiting info from code.
   *
   * @param \Drupal\commerce_recruiting\Code $code
   *   The recruiting code.
   *
   * @return \Drupal\commerce_recruiting\Entity\CampaignOptionInterface
   *   The campaignOption
   */
  public function findCampaignOptionFromCode(Code $code);

  /**
   * Saves and returns a recruiting session from code.
   *
   * @param \Drupal\commerce_recruiting\Code $code
   *   The recruiting code.
   *
   * @return \Drupal\commerce_recruiting\RecruitingSessionInterface
   *   The session service.
   */
  public function saveRecruitingSession(Code $code);

}
