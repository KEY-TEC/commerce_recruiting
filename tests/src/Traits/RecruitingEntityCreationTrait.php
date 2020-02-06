<?php

namespace Drupal\Tests\commerce_recruitment\Traits;

use Drupal\commerce_recruitment\Entity\RecruitingEntity;
use Drupal\commerce_recruitment\Entity\RecruitingEntityType;

/**
 * Provides methods to create recruiting entities.
 *
 * This trait is meant to be used only by test classes.
 */
trait RecruitingEntityCreationTrait {

  use FieldCreationTrait;

  /**
   * Install required product bundle / order type etc.
   */
  protected function installRecruitingEntity() {
    $recruiting_type = RecruitingEntityType::create([
      'id' => 'refered_friends',
    ]);
    $recruiting_type->save();
  }

  /**
   * Create an recruiting entity.
   *
   * @return \Drupal\commerce_recruitment\Entity\RecruitingEntityInterface
   *   The recruiting entity.
   */
  protected function createRecruitmentEntity(array $options = ['type' => 'redered_friends']) {
    $recruitment = RecruitingEntity::create($options);
    return $recruitment;
  }

}
