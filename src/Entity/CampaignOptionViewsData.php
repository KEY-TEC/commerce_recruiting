<?php

namespace Drupal\commerce_recruiting\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Recruiting option entities.
 */
class CampaignOptionViewsData extends EntityViewsData {

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
