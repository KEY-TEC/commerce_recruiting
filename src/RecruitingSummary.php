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
   * The campaign.
   *
   * @var \Drupal\commerce_recruiting\Entity\CampaignInterface
   */
  private $campaign;

  /**
   * @return string
   */
  public function getCampaign() {
    return $this->campaign;
  }
  /**
   * RecruitingSummary constructor.
   *
   * @param \Drupal\commerce_price\Price $total_price
   *   The total price.
   * @param $campaign
   *   The campaign.
   * @param array $results
   *   The results.
   */
  public function __construct(Price $total_price, $campaign, array $results = []) {
    $this->totalPrice = $total_price;
    $this->results = $results;
    $this->campaign = $campaign;
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
