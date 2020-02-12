<?php

namespace Drupal\commerce_recruitment\Entity;

use Drupal\commerce_price\Price;
use Drupal\commerce_promotion\Entity\PromotionInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\UserInterface;

/**
 * Provides an interface for defining recruiting entity entities.
 *
 * @ingroup commerce_recruitment
 */
interface RecruitingConfigInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the recruiting entity name.
   *
   * @return string
   *   Name of the recruiting entity.
   */
  public function getName();

  /**
   * Sets the recruiting entity name.
   *
   * @param string $name
   *   The recruiting entity name.
   *
   * @return \Drupal\commerce_recruitment\Entity\RecruitingEntityInterface
   *   The called recruiting entity entity.
   */
  public function setName($name);

  /**
   * Get whether the recruitment is enabled.
   *
   * @return bool
   *   TRUE if the recruitment is enabled, FALSE otherwise.
   */
  public function isEnabled();

  /**
   * Sets whether the recruitment is enabled.
   *
   * @param bool $enabled
   *   Whether the recruitment is enabled.
   *
   * @return $this
   */
  public function setEnabled($enabled);

  /**
   * Gets the recruitment config description description.
   *
   * @return string
   *   The recruitment description.
   */
  public function getDescription();

  /**
   * Sets the recruitment config description.
   *
   * @param string $description
   *   The config description.
   *
   * @return $this
   */
  public function setDescription($description);

  /**
   * Returns the recruiter user entity.
   *
   * @return \Drupal\user\UserInterface
   *   The recruiter entity.
   */
  public function getRecruiter();

  /**
   * Sets the recruiter user entity.
   *
   * @param \Drupal\user\UserInterface $account
   *   The recruiter user entity.
   *
   * @return $this
   */
  public function setRecruiter(UserInterface $account);

  /**
   * Returns the promotion entity.
   *
   * @return \Drupal\commerce_promotion\Entity\PromotionInterface
   *   The promotion entity.
   */
  public function getPromotion();

  /**
   * Sets the promotion entity.
   *
   * @param \Drupal\commerce_promotion\Entity\PromotionInterface $promotion
   *   The promotion entity.
   *
   * @return $this
   */
  public function setPromotion(PromotionInterface $promotion);

  /**
   * Gets the recruitment start date/time.
   *
   * @param string $store_timezone
   *   The store timezone. E.g. "Europe/Berlin".
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   The recruitment start date/time.
   */
  public function getStartDate($store_timezone = 'UTC');

  /**
   * Sets the recruitment start date/time.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $start_date
   *   The recruitment start date/time.
   *
   * @return $this
   */
  public function setStartDate(DrupalDateTime $start_date);

  /**
   * Gets the recruitment end date/time.
   *
   * @param string $store_timezone
   *   The store timezone. E.g. "Europe/Berlin".
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   The recruitment end date/time.
   */
  public function getEndDate($store_timezone = 'UTC');

  /**
   * Sets the recruitment end date/time.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $end_date
   *   The recruitment end date/time.
   *
   * @return $this
   */
  public function setEndDate(DrupalDateTime $end_date = NULL);

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
   * Gets the weight.
   *
   * @return int
   *   The weight.
   */
  public function getWeight();

  /**
   * Sets the weight.
   *
   * @param int $weight
   *   The weight.
   *
   * @return $this
   */
  public function setWeight($weight);


}
