<?php

namespace Drupal\commerce_recruiting;

use Drupal\commerce_recruiting\Entity\CampaignInterface;
use Drupal\commerce_recruiting\Entity\Invoice;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\user\Entity\User;

/**
 * Class InvoiceManager.
 */
class InvoiceManager implements InvoiceManagerInterface {

  /**
   * The recruiting manager.
   *
   * @var \Drupal\commerce_recruiting\RecruitingManagerInterface
   */
  protected $recruitingManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * InvoiceManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce_recruiting\RecruitingManagerInterface $recruiting_manager
   *   The recruiting manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RecruitingManagerInterface $recruiting_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->recruitingManager = $recruiting_manager;
  }

  /**
   * {@inheritDoc}
   */
  public function createInvoice(CampaignInterface $campaign, User $recruiter) {
    /** @var \Drupal\commerce_recruiting\Entity\Invoice $invoice */
    $invoice = Invoice::create(['name' => $campaign->getName()]);
    $recruitings = $this->recruitingManager->findRecruitingByCampaign($campaign, $recruiter, 'accepted');

    /** @var \Drupal\commerce_recruiting\Entity\RecruitingInterface $recruiting */
    foreach ($recruitings as $recruiting) {
      $invoice->addRecruiting($recruiting);
    }
    $invoice->save();
    return $invoice;
  }

  /**
   * {@inheritDoc}
   */
  public function findInvoices(User $recruiter) {
    return $this->entityTypeManager->getStorage('commerce_recruiting_invoice')->loadByProperties(['user_id' => $recruiter->id()]);
  }
}
