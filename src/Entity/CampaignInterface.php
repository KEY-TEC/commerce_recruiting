<?php

namespace Drupal\commerce_recruiting\Entity;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\UserInterface;

/**
 * Provides an interface for defining campaign entities.
 *
 * @ingroup commerce_recruiting
 */
interface CampaignInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the campaign name.
   *
   * @return string
   *   Name of the campaign.
   */
  public function getName();

  /**
   * Sets the campaign name.
   *
   * @param string $name
   *   The campaign name.
   *
   * @return $this
   */
  public function setName($name);

  /**
   * Get whether the campaign is enabled.
   *
   * @return bool
   *   TRUE if the campaign is enabled, FALSE otherwise.
   */
  public function isEnabled();

  /**
   * Sets whether the campaign is enabled.
   *
   * @param bool $enabled
   *   Whether the campaign is enabled.
   *
   * @return $this
   */
  public function setEnabled($enabled);

  /**
   * Gets the campaign description.
   *
   * @return string
   *   The campaign description.
   */
  public function getDescription();

  /**
   * Sets the campaign description.
   *
   * @param string $description
   *   The description.
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
   * Gets the campaign start date/time.
   *
   * @param string $store_timezone
   *   The store timezone. E.g. "Europe/Berlin".
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   The campaign start date/time.
   */
  public function getStartDate($store_timezone = 'UTC');

  /**
   * Sets the campaign start date/time.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $start_date
   *   The campaign start date/time.
   *
   * @return $this
   */
  public function setStartDate(DrupalDateTime $start_date);

  /**
   * Gets the campaign end date/time.
   *
   * @param string $store_timezone
   *   The store timezone. E.g. "Europe/Berlin".
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   The campaign end date/time.
   */
  public function getEndDate($store_timezone = 'UTC');

  /**
   * Sets the campaign end date/time.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $end_date
   *   The campaign end date/time.
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
