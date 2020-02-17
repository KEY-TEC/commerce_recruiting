<?php

namespace Drupal\commerce_recruiting;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Interface CampaignManagerInterface.
 */
interface CampaignManagerInterface {

  /**
   * Returns a campaign option.
   *
   * The method will try to find and use the first fitting recruiting
   * campaign option that has no recruiter and matches the given product
   * if given.
   *
   * @param \Drupal\Core\Session\AccountInterface|null $recruiter
   *   Optional filter configs by recruiter.
   * @param \Drupal\Core\Entity\EntityInterface|null $product
   *   Optional filter configs by product.
   *
   * @return \Drupal\commerce_recruiting\Entity\CampaignOptionInterface[]
   *   The found recruiting campaign option.
   */
  public function findCampaignOptions(AccountInterface $recruiter = NULL, EntityInterface $product = NULL);

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
   * Returns recruiting session from code.
   *
   * @param \Drupal\commerce_recruiting\Code $code
   *   The recruiting code.
   *
   * @return \Drupal\commerce_recruiting\RecruitingSessionInterface
   *   The session service.
   */
  public function getSessionFromCode(Code $code);

}
