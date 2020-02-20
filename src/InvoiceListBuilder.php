<?php

namespace Drupal\commerce_recruiting;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Invoice entities.
 *
 * @ingroup commerce_recruiting
 */
class InvoiceListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Invoice ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\commerce_recruiting\Entity\Invoice $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.commerce_recruitment_invoice.edit_form',
      ['commerce_recruitment_invoice' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
