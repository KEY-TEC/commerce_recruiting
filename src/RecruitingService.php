<?php

namespace Drupal\commerce_recruitment;

use Drupal\commerce_recruitment\Entity\RecruitingEntity;

/**
 * Class RecruitingService.
 */
class RecruitingService implements RecruitingServiceInterface {

  /**
   * {@inheritDoc}
   */
  public function getTotalBonusPerUser($uid, $include_paid_out = FALSE, $recruitment_type = NULL) {
    $query = \Drupal::entityQuery('recruiting')
      ->condition('recruiter', $uid)
      ->condition('is_paid_out', (string) $include_paid_out);

    if ($recruitment_type !== NULL) {
      $query->condition('type', $recruitment_type);
    }

    $recruiting_ids = $query->execute();
    $recruitings = RecruitingEntity::loadMultiple($recruiting_ids);
    $total_price = NULL;
    foreach ($recruitings as $recruit) {
      /* @var \Drupal\commerce_recruitment\Entity\RecruitingEntityInterface $recruit */
      if ($bonus = $recruit->getBonus()->toPrice()) {
        $total_price = $total_price ? $total_price->add($bonus) : $bonus;
      }
    }
    return $total_price;
  }

}
