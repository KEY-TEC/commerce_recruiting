<?php

namespace Drupal\commerce_recruiting;

use Drupal\commerce_price\Price;

/**
 * Class RecruitingSummary.
 */
class RecruitingSummary {

  /**
   * The total price.
   *
   * @var \Drupal\commerce_price\Price
   */
  private $totalPrice;

  /**
   * The results.
   *
   * @var array
   */
  private $results;

  /**
   * RecruitingSummary constructor.
   *
   * @param \Drupal\commerce_price\Price $total_price
   *   The total price.
   * @param array $results
   *   The results.
   */
  public function __construct(Price $total_price, array $results = []) {
    $this->totalPrice = $total_price;
    $this->results = $results;
  }

  /**
   * Gets the total price.
   *
   * @return \Drupal\commerce_price\Price
   *   The total price.
   */
  public function getTotalPrice() {
    return $this->totalPrice;
  }

  /**
   * Gets the result.
   *
   * @return array
   *   The results.
   */
  public function getResults() {
    return $this->results;
  }

}
