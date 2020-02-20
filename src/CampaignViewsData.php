<?php

namespace Drupal\commerce_recruiting;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for campaign entities.
 */
class CampaignViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
