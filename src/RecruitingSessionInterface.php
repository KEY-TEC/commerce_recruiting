<?php

namespace Drupal\commerce_recruitment;

/**
 * Stores the active recruiting session.
 *
 * @see \Drupal\commerce_recruitment\RecruitingSessionInterface
 */
interface RecruitingSessionInterface {

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

}
