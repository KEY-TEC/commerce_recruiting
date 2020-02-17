<?php

namespace Drupal\commerce_recruiting\Entity;

use Drupal\commerce_promotion\Entity\PromotionInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\UserInterface;

/**
 * Provides an interface for defining recruiting entity entities.
 *
 * @ingroup commerce_recruiting
 */
interface CampaignInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

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
   * @return \Drupal\commerce_recruiting\Entity\RecruitingInterface
   *   The called recruiting entity entity.
   */
  public function setName($name);

  /**
   * Get whether the recruiting is enabled.
   *
   * @return bool
   *   TRUE if the recruiting is enabled, FALSE otherwise.
   */
  public function isEnabled();

  /**
   * Sets whether the recruiting is enabled.
   *
   * @param bool $enabled
   *   Whether the recruiting is enabled.
   *
   * @return $this
   */
  public function setEnabled($enabled);

  /**
   * Gets the recruiting campaign description description.
   *
   * @return string
   *   The recruiting description.
   */
  public function getDescription();

  /**
   * Sets the recruiting campaign description.
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
   * Gets the recruiting start date/time.
   *
   * @param string $store_timezone
   *   The store timezone. E.g. "Europe/Berlin".
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   The recruiting start date/time.
   */
  public function getStartDate($store_timezone = 'UTC');

  /**
   * Sets the recruiting start date/time.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $start_date
   *   The recruiting start date/time.
   *
   * @return $this
   */
  public function setStartDate(DrupalDateTime $start_date);

  /**
   * Gets the recruiting end date/time.
   *
   * @param string $store_timezone
   *   The store timezone. E.g. "Europe/Berlin".
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   The recruiting end date/time.
   */
  public function getEndDate($store_timezone = 'UTC');

  /**
   * Sets the recruiting end date/time.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $end_date
   *   The recruiting end date/time.
   *
   * @return $this
   */
  public function setEndDate(DrupalDateTime $end_date = NULL);

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

  /**
   * Gets the options.
   *
   * @return \Drupal\commerce_recruiting\Entity\CampaignOptionInterface[]
   *   The options.
   */
  public function getOptions();

  /**
   * Gets the first option.
   *
   * @return \Drupal\commerce_recruiting\Entity\CampaignOptionInterface
   *   The option.
   */
  public function getFirstOption();

  /**
   * Sets the options.
   *
   * @param \Drupal\commerce_recruiting\Entity\CampaignOptionInterface[] $options
   *   The options.
   *
   * @return $this
   */
  public function setOptions(array $options);

  /**
   * Gets whether the campaign has options.
   *
   * @return bool
   *   TRUE if the option has options, FALSE otherwise.
   */
  public function hasOptions();

  /**
   * Adds an order item.
   *
   * @param \Drupal\commerce_recruiting\Entity\CampaignOptionInterface $option
   *   The option.
   *
   * @return $this
   */
  public function addOption(CampaignOptionInterface $option);

  /**
   * Removes an option.
   *
   * @param \Drupal\commerce_recruiting\Entity\CampaignOptionInterface $option
   *   The option.
   *
   * @return $this
   */
  public function removeOption(CampaignOptionInterface $option);

  /**
   * Checks whether the order has a given option.
   *
   * @param \Drupal\commerce_recruiting\Entity\CampaignOptionInterface $option
   *   The option.
   *
   * @return bool
   *   TRUE if the option was found, FALSE otherwise.
   */
  public function hasOption(CampaignOptionInterface $option);

}
