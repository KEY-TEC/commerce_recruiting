<?php

namespace Drupal\commerce_recruiting\Entity;

use Drupal\commerce_price\Price;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Invoice entities.
 *
 * @ingroup commerce_recruiting
 */
interface InvoiceInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Invoice name.
   *
   * @return string
   *   Name of the Invoice.
   */
  public function getName();

  /**
   * Sets the Invoice name.
   *
   * @param string $name
   *   The Invoice name.
   *
   * @return \Drupal\commerce_recruiting\Entity\InvoiceInterface
   *   The called Invoice entity.
   */
  public function setName($name);

  /**
   * Gets the Invoice creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Invoice.
   */
  public function getCreatedTime();

  /**
   * Sets the Invoice creation timestamp.
   *
   * @param int $timestamp
   *   The Invoice creation timestamp.
   *
   * @return \Drupal\commerce_recruiting\Entity\InvoiceInterface
   *   The called Invoice entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the invoice price entity.
   *
   * @return \Drupal\commerce_price\Price
   *   The bonus price entity.
   */
  public function getPrice();

  /**
   * Sets the invoice price entity.
   *
   * @param \Drupal\commerce_price\Price $price
   *   The bonus price entity.
   *
   * @return $this
   */
  public function setPrice(Price $price);

  /**
   * Gets the invoice state.
   *
   * @return \Drupal\state_machine\Plugin\Field\FieldType\StateItemInterface
   *   The recruiting state.
   */
  public function getState();

  /**
   * Sets the invoice state.
   *
   * @param string $state_id
   *   The new state ID.
   *
   * @return $this
   */
  public function setState($state_id);

  /**
   * Gets the recrutings.
   *
   * @return \Drupal\commerce_recruiting\Entity\RecruitingInterface[]
   *   The recrutings.
   */
  public function getRecruitings();

  /**
   * Gets the first recruting.
   *
   * @return \Drupal\commerce_recruiting\Entity\RecruitingInterface
   *   The recruting.
   */
  public function getFirstRecruiting();

  /**
   * Sets the recrutings.
   *
   * @param \Drupal\commerce_recruiting\Entity\RecruitingInterface[] $recrutings
   *   The recrutings.
   *
   * @return $this
   */
  public function setRecruitings(array $recrutings);

  /**
   * Gets whether the campaign has recrutings.
   *
   * @return bool
   *   TRUE if the recruting has recrutings, FALSE otherwise.
   */
  public function hasRecruitings();

  /**
   * Adds an order item.
   *
   * @param \Drupal\commerce_recruiting\Entity\RecruitingInterface $recruting
   *   The recruting.
   *
   * @return $this
   */
  public function addRecruiting(RecruitingInterface $recruting);

  /**
   * Removes an recruting.
   *
   * @param \Drupal\commerce_recruiting\Entity\RecruitingInterface $recruting
   *   The recruting.
   *
   * @return $this
   */
  public function removeRecruiting(RecruitingInterface $recruting);

  /**
   * Checks whether the order has a given recruting.
   *
   * @param \Drupal\commerce_recruiting\Entity\RecruitingInterface $recruting
   *   The recruting.
   *
   * @return bool
   *   TRUE if the recruting was found, FALSE otherwise.
   */
  public function hasRecruiting(RecruitingInterface $recruting);

}
