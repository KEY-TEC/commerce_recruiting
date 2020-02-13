<?php

namespace Drupal\commerce_recruitment;

use Drupal\commerce_recruitment\Entity\RecruitingConfig;
use Drupal\user\Entity\User;

/**
 * Interface RecruitingManagerInterface.
 */
interface RecruitingManagerInterface {

  /**
   * Calculates the sum of all recruiting bonus of an user.
   *
   * @param int $uid
   *   User ID.
   * @param bool $include_paid_out
   *   Set true to include already paid out recruiting bonus.
   * @param string $recruitment_type
   *   Filter by recruitment type. Leave empty to include all types.
   *
   * @return int
   *   The total bonus.
   */
  public function getTotalBonusPerUser($uid, $include_paid_out = FALSE, $recruitment_type = NULL);

  /**
   * Returns recruiting info from code.
   *
   * @param string $code
   *   The recruiting code.
   *
   * @return array
   *   Keys:
   *    - recruiter
   *    - recruiting_config
   */
  public function getRecruitingInfoFromCode($code);

  /**
   * Returns the recruiting url.
   *
   * @param \Drupal\commerce_recruitment\Entity\RecruitingConfig $recruiting_config
   *   The recruiting config.
   * @param \Drupal\user\Entity\User $recruiter
   *   The recruiter.
   *
   * @return \Drupal\Core\Url
   *   The short url.
   */
  public function getRecruitingUrl(RecruitingConfig $recruiting_config, User $recruiter = NULL);

  /**
   * Returns the recruiting code.
   *
   * @param \Drupal\commerce_recruitment\Entity\RecruitingConfig $recruiting_config
   *   The recruiting config.
   * @param \Drupal\user\Entity\User $recruiter
   *   The recruiter.
   *
   * @return string
   *   The code.
   */
  public function getRecruitingCode(RecruitingConfig $recruiting_config, User $recruiter = NULL);

}
