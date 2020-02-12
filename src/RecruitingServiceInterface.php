<?php

namespace Drupal\commerce_recruitment;

/**
 * Interface RecruitingServiceInterface.
 */
interface RecruitingServiceInterface {

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
   * @return mixed
   */
  public function getTotalBonusPerUser($uid, $include_paid_out = FALSE, $recruitment_type = NULL);

}
