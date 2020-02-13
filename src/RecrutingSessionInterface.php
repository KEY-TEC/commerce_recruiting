<?php

namespace Drupal\commerce_recruitment;

/**
 * Stores the active recruting session.
 *
 * @see \Drupal\commerce_recruitment\RecrutingSessionInterface
 */
interface RecrutingSessionInterface {

  /**
   * Gets the recruter.
   *
   * @return \Drupal\user\Entity\User
   *   The recruter.
   */
  public function getRecruter();

  /**
   * Gets the recruting config.
   *
   * @return \Drupal\commerce_recruitment\Entity\RecruitingConfig
   *   The recruting config.
   */
  public function getRecrutingConfig();

}
