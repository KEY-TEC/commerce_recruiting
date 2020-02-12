<?php

namespace Drupal\commerce_recruitment\Resolver;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Returns the first recruiting config found by the current product.
 */
class DefaultRecruitingConfigResolver implements RecruitingConfigResolverInterface {

  /**
   * Constructs a new DefaultRecruitmentConfigResolver object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
  }

  /**
   * {@inheritdoc}
   */
  public function resolve() {
    return NULL;
  }

}
