<?php

namespace Drupal\commerce_recruitment\Entity;

use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\UserInterface;

/**
 * Provides an interface for defining recruiting entity entities.
 *
 * @ingroup commerce_recruitment
 */
interface RecruitingEntityInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

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
   * Gets the recruiting entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the recruiting entity.
   */
  public function getCreatedTime();

  /**
   * Sets the recruiting entity creation timestamp.
   *
   * @param int $timestamp
   *   The recruiting entity creation timestamp.
   *
   * @return \Drupal\commerce_recruitment\Entity\RecruitingEntityInterface
   *   The called recruiting entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the recruited user entity.
   *
   * @return \Drupal\user\UserInterface
   *   The recruited user entity.
   */
  public function getRecruited();

  /**
   * Sets the recruited user entity.
   *
   * @param \Drupal\user\UserInterface $account
   *   The recruited user entity.
   *
   * @return $this
   */
  public function setRecruited(UserInterface $account);

  /**
   * Returns the recruited user ID.
   *
   * @return int|null
   *   The recruited user ID, or NULL
   *   in case the user ID field has not been set on the entity.
   */
  public function getRecruitedId();

  /**
   * Sets the recruited user ID.
   *
   * @param int $uid
   *   The recruited user id.
   *
   * @return $this
   */
  public function setRecruitedId($uid);

  /**
   * Returns the product entity.
   *
   * @return \Drupal\commerce_product\Entity\ProductInterface
   *   The product entity.
   */
  public function getProduct();

  /**
   * Sets the product entity.
   *
   * @param \Drupal\commerce_product\Entity\ProductInterface $product
   *   The product entity.
   *
   * @return $this
   */
  public function setProduct(ProductInterface $product);

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
   * Gets the recruiting state.
   *
   * @return \Drupal\state_machine\Plugin\Field\FieldType\StateItemInterface
   *   The recruiting state.
   */
  public function getState();

  /**
   * Sets the recruiting state.
   *
   * @param string $state_id
   *   The new state ID.
   *
   * @return $this
   */
  public function setState($state_id);

  /**
   * Get the order.
   *
   * @return \Drupal\commerce_order\Entity\Order
   *   The order.
   */
  public function getOrder();

  /**
   * Returns the order item.
   *
   * @return \Drupal\commerce_order\Entity\OrderItemInterface
   *   The associated order item.
   */
  public function getOrderItem();

}
