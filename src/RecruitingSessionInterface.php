<?php

namespace Drupal\commerce_recruitment;

use Drupal\commerce_recruitment\Entity\RecruitingConfig;
use Drupal\user\Entity\User;

/**
 * Stores the active recruiting session.
 *
 * @see \Drupal\commerce_recruitment\RecruitingSessionInterface
 */
interface RecruitingSessionInterface {
  // The recruiting session types.
  const RECRUITER = 'recruiter';
  const RECRUITING_CONFIG = 'recruiting_config';

  /**
   * Gets the recruiter.
   *
   * @return \Drupal\user\Entity\User
   *   The recruiter.
   */
  public function getRecruiter();

  /**
   * Gets the recruiting config.
   *
   * @return \Drupal\commerce_recruitment\Entity\RecruitingConfig
   *   The recruiting config.
   */
  public function getRecruitingConfig();

  /**
   * Sets the recruiter.
   *
   * @param \Drupal\user\Entity\User $recruiter
   *   The recruiter.
   */
  public function setRecruiter(User $recruiter);

  /**
   * Sets the recruiting config.
   *
   * @param \Drupal\commerce_recruitment\Entity\RecruitingConfig $recruiting
   *   The recruiting config.
   */
  public function setRecruitingConfig(RecruitingConfig $recruiting);

}
