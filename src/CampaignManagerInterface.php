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
   *   List of campaigns.
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
   *   List of campaigns.
   */
  public function findNoRecruiterCampaigns(EntityInterface $product = NULL);

  /**
   * Returns a campaign option from code.
   *
   * @param \Drupal\commerce_recruiting\Code $code
   *   The recruitment code.
   *
   * @return \Drupal\commerce_recruiting\Entity\CampaignOptionInterface
   *   The campaign option.
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
   * Saves and returns a recruitment session from code.
   *
   * @param \Drupal\commerce_recruiting\Code $code
   *   The recruitment code.
   *
   * @return \Drupal\commerce_recruiting\RecruitmentSessionInterface
   *   The session service.
   */
  public function saveRecruitmentSession(Code $code);

}
