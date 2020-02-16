<?php

namespace Drupal\commerce_recruitment;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Recruiting entity entities.
 *
 * @ingroup commerce_recruitment
 */
class RecruitingConfigListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Recruiting config ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\commerce_recruitment\Entity\Recruiting $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.commerce_recruiting_config.edit_form',
      ['commerce_recruiting_config' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
