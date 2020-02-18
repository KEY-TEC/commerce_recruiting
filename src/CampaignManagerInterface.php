<?php

namespace Drupal\commerce_recruiting;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Interface CampaignManagerInterface.
 */
interface CampaignManagerInterface {

  /**
   * Returns recruiter specific campaigns.
   *
   * @param \Drupal\Core\Session\AccountInterface $recruiter
   *   The recruiter.
   *
   * @return \Drupal\commerce_recruiting\Entity\CampaignInterface[]
   *   The found recruiting campaign option.
   */
  public function findRecruiterCampaigns(AccountInterface $recruiter = NULL);

  /**
   * Returns campaigns without specific recruiter filtered by the given product.
   *
   * These campaigns are used for "recruit a friend".
   *
   * @param \Drupal\Core\Entity\EntityInterface|null $product
   *   Optional filter configs by product.
   *
   * @return \Drupal\commerce_recruiting\Entity\CampaignInterface[]
   *   The found recruiting campaign option.
   */
  public function findNoRecruiterCampaigns(EntityInterface $product = NULL);

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
   * Returns the recruiter that is associated with the given code.
   *
   * @param \Drupal\commerce_recruiting\Code $code
   *   The code.
   *
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\user\UserInterface|null
   *   The recruiter or null.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getRecruiterFromCode(Code $code);

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
