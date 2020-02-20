<?php

namespace Drupal\commerce_recruiting\Entity;

use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\UserInterface;

/**
 * Provides an interface for defining recruitment entities.
 *
 * @ingroup commerce_recruiting
 */
interface RecruitmentInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the recruitment entity name.
   *
   * @return string
   *   Name of the recruitment entity.
   */
  public function getName();

  /**
   * Sets the recruitment entity name.
   *
   * @param string $name
   *   The recruitment entity name.
   *
   * @return $this
   */
  public function setName($name);

  /**
   * Gets the recruitment entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the recruitment entity.
   */
  public function getCreatedTime();

  /**
   * Sets the recruitment entity creation timestamp.
   *
   * @param int $timestamp
   *   The recruitment entity creation timestamp.
   *
   * @return $this
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
   * Gets the recruitment state.
   *
   * @return \Drupal\state_machine\Plugin\Field\FieldType\StateItemInterface
   *   The recruitment state.
   */
  public function getState();

  /**
   * Sets the recruitment state.
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
