<?php

namespace Drupal\commerce_recruitment\Resolver;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Returns the first recruiting config found by the current product.
 */
class DefaultRecruitingConfigResolver implements RecruitingConfigResolverInterface {

  /**
   * The recruiting config entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $configStorage;

  /**
   * Constructs a new DefaultRecruitmentConfigResolver object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->configStorage = $entity_type_manager->getStorage('commerce_recruiting_config');

  }

  /**
   * {@inheritdoc}
   */
  public function resolve() {
    $results = $this->configStorage->loadByProperties();
    return NULL;
  }

}
