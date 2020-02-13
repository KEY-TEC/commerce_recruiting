<?php

namespace Drupal\Tests\commerce_recruitment\Traits;

use Drupal\commerce_recruitment\Entity\RecruitingConfig;
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
      'id' => 'default',
    ]);
    $recruiting_type->save();
  }

  /**
   * Install required product bundle / order type etc.
   */
  protected function installRecruitingConfig() {
    $recruiting_type = RecruitingConfig::create([
      'id' => 'default',
    ]);
    $recruiting_type->save();
  }

  /**
   * Create an recruiting entity.
   *
   * @return \Drupal\commerce_recruitment\Entity\RecruitingEntityInterface
   *   The recruiting entity.
   */
  protected function createRecruitmentEntity(array $options = ['type' => 'default', 'name' => 'test']) {
    $recruitment = RecruitingEntity::create($options);
    return $recruitment;
  }

  /**
   * Create an recruiting entity.
   *
   * @return \Drupal\commerce_recruitment\Entity\RecruitingConfig
   *   The recruiting entity.
   */
  protected function createRecruitmentConfig(array $options = ['type' => 'default', 'name' => 'test']) {
    $recruitment = RecruitingConfig::create($options);
    return $recruitment;
  }

}
