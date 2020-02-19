<?php

namespace Drupal\commerce_recruiting;

use Drupal\commerce_recruiting\Entity\CampaignInterface;
use Drupal\user\Entity\User;

/**
 * Interface InvoiceManagerInterface.
 */
interface InvoiceManagerInterface {

  /**
   * Create a invoice for given campaign.
   *
   * @param \Drupal\commerce_recruiting\Entity\CampaignInterface $campaign
   *   The campaign.
   * @param \Drupal\user\Entity\User $recruiter
   *   The recruiter.
   *
   * @return \Drupal\commerce_recruiting\Entity\InvoiceInterface
   *   The created invoice.
   */
  public function createInvoice(CampaignInterface $campaign, User $recruiter);

  /**
   * Find invoices.
   *
   * @param \Drupal\user\Entity\User $recruiter
   *   The recruiter
   *
   * @return \Drupal\commerce_recruiting\Entity\InvoiceInterface
   *   The invoices.
   */
  public function findInvoices(User $recruiter);

}
