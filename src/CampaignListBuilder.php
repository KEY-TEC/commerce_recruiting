<?php

namespace Drupal\commerce_recruiting;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of campaign entities.
 *
 * @ingroup commerce_recruiting
 */
class CampaignListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Campaign ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\commerce_recruiting\Entity\Recruitment $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.commerce_recruitment_campaign.edit_form',
      ['commerce_recruitment_campaign' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
