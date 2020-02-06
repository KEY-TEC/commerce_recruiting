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
class RecruitingEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Recruiting entity ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\commerce_recruitment\Entity\RecruitingEntity $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.recruiting.edit_form',
      ['recruiting' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
