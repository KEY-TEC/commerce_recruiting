<?php

namespace Drupal\commerce_recruiting\Entity;

use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_price\Price;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Recruiting option entities.
 *
 * @ingroup commerce_recruiting
 */
interface CampaignOptionInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  const RECRUIT_BONUS_METHOD_FIX = 'fix';

  const RECRUIT_BONUS_METHOD_PERCENT = 'percent';

  /**
   * Gets the recruiting code.
   *
   * @return string
   *   Code of the recruiting option.
   */
  public function getCode();

  /**
   * Sets the recruiting entity name.
   *
   * @param string $code
   *   The recruiting code.
   *
   * @return \Drupal\commerce_recruiting\Entity\RecruitingInterface
   *   The called recruiting entity entity.
   */
  public function setCode($code);

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Recruiting option creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Recruiting option.
   */
  public function getCreatedTime();

  /**
   * Sets the Recruiting option creation timestamp.
   *
   * @param int $timestamp
   *   The Recruiting option creation timestamp.
   *
   * @return \Drupal\commerce_recruiting\Entity\CampaignOptionInterface
   *   The called Recruiting option entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the bonus price entity.
   *
   * @return \Drupal\commerce_price\Price
   *   The bonus price entity.
   */
  public function getBonus();

  /**
   * Sets the bonus price entity.
   *
   * @param \Drupal\commerce_price\Price $price
   *   The bonus price entity.
   *
   * @return $this
   */
  public function setBonus(Price $price);

  /**
   * Gets the bonus proc.
   *
   * @return int
   *   The bonus in percent.
   */
  public function getBonusPercent();

  /**
   * Calculates the bonus.
   *
   * @param \Drupal\commerce_order\Entity\OrderItemInterface $order_item
   *   The bonus.
   *
   * @return \Drupal\commerce_price\Price
   *   The calculated bonus.
   */
  public function calculateBonus(OrderItemInterface $order_item);

  /**
   * Returns the product or product bundle.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The product or product bundle entity.
   */
  public function getProduct();

  /**
   * Returns the campaign.
   *
   * @return \Drupal\commerce_recruiting\Entity\CampaignInterface
   *   The campaign.
   */
  public function getCampaign();

  /**
   * Set the product entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $product
   *   The product entity.
   *
   * @return $this
   */
  public function setProduct(EntityInterface $product);

}
