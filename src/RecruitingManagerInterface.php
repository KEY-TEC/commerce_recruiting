<?php

namespace Drupal\commerce_recruitment;

use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\Core\Session\AccountInterface;

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
   * @return mixed
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

}
