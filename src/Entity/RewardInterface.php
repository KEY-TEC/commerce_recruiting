<?php

namespace Drupal\commerce_recruiting\Entity;

use Drupal\commerce_price\Price;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining reward entities.
 *
 * @ingroup commerce_recruiting
 */
interface RewardInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Gets the reward name.
   *
   * @return string
   *   Name of the reward.
   */
  public function getName();

  /**
   * Sets the reward name.
   *
   * @param string $name
   *   The reward name.
   *
   * @return $this
   */
  public function setName($name);

  /**
   * Gets the reward creation timestamp.
   *
   * @return int
   *   Creation timestamp of the reward.
   */
  public function getCreatedTime();

  /**
   * Sets the reward creation timestamp.
   *
   * @param int $timestamp
   *   The reward creation timestamp.
   *
   * @return $this
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the reward price entity.
   *
   * @return \Drupal\commerce_price\Price
   *   The reward price entity.
   */
  public function getPrice();

  /**
   * Sets the reward price entity.
   *
   * @param \Drupal\commerce_price\Price $price
   *   The reward price entity.
   *
   * @return $this
   */
  public function setPrice(Price $price);

  /**
   * Gets the reward state.
   *
   * @return \Drupal\state_machine\Plugin\Field\FieldType\StateItemInterface
   *   The reward state.
   */
  public function getState();

  /**
   * Sets the reward state.
   *
   * @param string $state_id
   *   The new state ID.
   *
   * @return $this
   */
  public function setState($state_id);

  /**
   * Gets the recruitments.
   *
   * @return \Drupal\commerce_recruiting\Entity\RecruitmentInterface[]
   *   The recruitments.
   */
  public function getRecruitments();

  /**
   * Gets the first recruitment.
   *
   * @return \Drupal\commerce_recruiting\Entity\RecruitmentInterface
   *   The recruitment.
   */
  public function getFirstRecruitment();

  /**
   * Sets the recruitments.
   *
   * @param \Drupal\commerce_recruiting\Entity\RecruitmentInterface[] $recruitments
   *   List of recruitment entities.
   *
   * @return $this
   */
  public function setRecruitments(array $recruitments);

  /**
   * Checks if recruitments are set.
   *
   * @return bool
   *   TRUE if the reward has recruitments, FALSE otherwise.
   */
  public function hasRecruitments();

  /**
   * Adds an order item.
   *
   * @param \Drupal\commerce_recruiting\Entity\RecruitmentInterface $recruitment
   *   The recruitment.
   *
   * @return $this
   */
  public function addRecruitment(RecruitmentInterface $recruitment);

  /**
   * Removes an recruitment.
   *
   * @param \Drupal\commerce_recruiting\Entity\RecruitmentInterface $recruitment
   *   The recruitment.
   *
   * @return $this
   */
  public function removeRecruitment(RecruitmentInterface $recruitment);

  /**
   * Checks whether the order has a given recruitment.
   *
   * @param \Drupal\commerce_recruiting\Entity\RecruitmentInterface $recruitment
   *   The recruitment.
   *
   * @return bool
   *   TRUE if the recruitment was found, FALSE otherwise.
   */
  public function hasRecruitment(RecruitmentInterface $recruitment);

}
