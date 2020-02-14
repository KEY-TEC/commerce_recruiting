<?php

namespace Drupal\commerce_recruitment;

use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\commerce_recruitment\Entity\RecruitingConfig;
use Drupal\user\Entity\User;

/**
 * Interface RecruitingManagerInterface.
 */
interface RecruitingManagerInterface {

  /**
   * Returns the "recruit a friend" link.
   *
   * The code in the link differs per account and cannot be created for
   * anonymous user.
   * The method will try to find and use the first fitting recruiting config.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account to create the sharing link for. Leave empty for current user.
   * @param \Drupal\commerce_product\Entity\ProductInterface $product
   *   Optional filter configs by product.
   *
   * @return \Drupal\Core\Url
   *   The short url.
   */
  public function getPublicRecruitingLink(AccountInterface $account = NULL, ProductInterface $product = NULL);

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
   * Create recruiting entity.
   *
   * @return \Drupal\commerce_recruitment\Entity\RecruitingEntity
   *   The recruiting entity
   */
  public function createRecruiting();

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
   * Returns recruiting session from code.
   *
   * @param string $code
   *   The recruiting code.
   *
   * @return \Drupal\commerce_recruitment\RecruitingSessionInterface
   *   The session service.
   */
  public function getRecruitingSessionFromCode($code);

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
