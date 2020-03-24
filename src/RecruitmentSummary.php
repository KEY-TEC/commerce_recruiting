<?php

namespace Drupal\commerce_recruiting;

use Drupal\commerce_price\Price;
use Drupal\commerce_recruiting\Entity\CampaignInterface;

/**
 * Class RecruitmentSummary.
 */
class RecruitmentSummary {

  /**
   * The total price.
   *
   * @var \Drupal\commerce_price\Price
   */
  private $totalPrice;

  /**
   * The campaign.
   *
   * @var \Drupal\commerce_recruiting\Entity\CampaignInterface
   */
  private $campaign;

  /**
   * The count.
   *
   * @var int
   */
  private $count = 0;

  /**
   * List of recruitment results.
   *
   * @var \Drupal\commerce_recruiting\RecruitmentResult[]
   */
  private $results;

  /**
   * RecruitmentSummary constructor.
   *
   * @param \Drupal\commerce_price\Price $total_price
   *   The total price.
   * @param \Drupal\commerce_recruiting\Entity\CampaignInterface $campaign
   *   The campaign.
   * @param int $result_count
   *   The all together result count.
   * @param \Drupal\commerce_recruiting\RecruitmentResult[] $results
   *   The results.
   */
  public function __construct(Price $total_price, CampaignInterface $campaign, $result_count = 0, array $results = []) {
    $this->totalPrice = $total_price;
    $this->campaign = $campaign;
    $this->count = $result_count;
    $this->results = $results;
  }

  /**
   * Returns the campaign.
   *
   * @return \Drupal\commerce_recruiting\Entity\CampaignInterface
   */
  public function getCampaign() {
    return $this->campaign;
  }

  /**
   * Returns the result count.
   *
   * @return int
   *   The count.
   */
  public function getCount() {
    return $this->count;
  }

  /**
   * Returns the total price.
   *
   * @return \Drupal\commerce_price\Price
   *   The total price.
   */
  public function getTotalPrice() {
    return $this->totalPrice;
  }

  /**
   * Returns the recruitment results.
   *
   * @return \Drupal\commerce_recruiting\RecruitmentResult[]
   *   The results.
   */
  public function getResults() {
    return $this->results;
  }

  /**
   * Checks if this summary has results.
   *
   * @return bool
   *   TRUE or FALSE.
   */
  public function hasResults() {
    return count($this->results) != 0;
  }

}
