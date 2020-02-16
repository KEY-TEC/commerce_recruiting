<?php

namespace Drupal\commerce_recruitment;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_price\Price;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\commerce_recruitment\Entity\RecruitingConfig;
use Drupal\user\Entity\User;

/**
 * Interface RecruitingManagerInterface.
 */
interface RecruitingManagerInterface {

  /**
   * Returns a recruiting confi.
   *
   * The method will try to find and use the first fitting recruiting config
   * that has no recruiter and matches the given product if given.
   *
   * @param \Drupal\Core\Session\AccountInterface|null $recruiter
   *   Optional filter configs by recruiter.
   * @param \Drupal\Core\Entity\EntityInterface|null $product
   *   Optional filter configs by product.
   *
   * @return \Drupal\commerce_recruitment\Entity\RecruitingConfig
   *   The found recruiting config.
   */
  public function findRecruitingConfig(AccountInterface $recruiter = NULL, EntityInterface $product = NULL);

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
   *   The order item.
   *
   * @param \Drupal\commerce_order\Entity\OrderItemInterface $order_item
   *   The responding order item.
   * @param \Drupal\user\Entity\User $recruiter
   *   The recruiter user.
   * @param \Drupal\user\Entity\User $recruited
   *   The recruited user.
   * @param \Drupal\commerce_recruitment\Entity\RecruitingConfig $config
   *   The recruiting config.
   * @param \Drupal\commerce_price\Price $bonus
   *   The bonus.
   *
   * @return \Drupal\commerce_recruitment\Entity\Recruiting
   *   The recruiting entity
   */
  public function createRecruiting(OrderItemInterface $order_item, User $recruiter, User $recruited, RecruitingConfig $config, Price $bonus);

  /**
   * Checks the order for recommend products in RecruitingSession.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order.
   *
   * @return array
   *   keys:
   *    0
   *      - order_item
   *      - recruiting_config
   *    etc.
   */
  public function sessionMatch(OrderInterface $order);

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
   * @param \Drupal\Core\Session\AccountInterface $recruiter
   *   The recruiter.
   *
   * @return \Drupal\Core\Url
   *   The short url.
   */
  public function getRecruitingUrl(RecruitingConfig $recruiting_config, AccountInterface $recruiter = NULL);

  /**
   * Returns the recruiting code.
   *
   * @param \Drupal\commerce_recruitment\Entity\RecruitingConfig $recruiting_config
   *   The recruiting config.
   * @param \Drupal\Core\Session\AccountInterface $recruiter
   *   The recruiter.
   *
   * @return string
   *   The code.
   */
  public function getRecruitingCode(RecruitingConfig $recruiting_config, AccountInterface $recruiter = NULL);

}
