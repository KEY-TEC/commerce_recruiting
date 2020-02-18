<?php

namespace Drupal\commerce_recruiting;

use Drupal\commerce_price\Price;

/**
 * Class RecruitingResult.
 */
class RecruitingResult {

  /**
   * The title.
   *
   * @var string
   */
  private $title;

  /**
   * The price.
   *
   * @var \Drupal\commerce_price\Price
   */
  private $price;

  /**
   * RecruitingResult constructor.
   *
   * @param string $title
   *   The title.
   * @param \Drupal\commerce_price\Price $price
   *   The price.
   */
  public function __construct($title, Price $price) {
    $this->title = $title;
    $this->price = $price;
  }

  /**
   * Gets the title.
   *
   * @return string
   *   The title.
   */
  public function getTitle() {
    return $this->title;
  }

  /**
   * Gets the price.
   *
   * @return \Drupal\commerce_price\Price
   *   The price.
   */
  public function getPrice() {
    return $this->price;
  }

  /**
   * Adds given price.
   *
   * @param \Drupal\commerce_price\Price $price
   *   The price.
   */
  public function addPrice(Price $price) {
    $this->price = $price->add($price);
  }

}
