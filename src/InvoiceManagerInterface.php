<?php

namespace Drupal\commerce_recruiting;

use Drupal\commerce_recruiting\Entity\CampaignInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Interface InvoiceManagerInterface.
 */
interface InvoiceManagerInterface {

  /**
   * Create a invoice for given campaign.
   *
   * @param \Drupal\commerce_recruiting\Entity\CampaignInterface $campaign
   *   The campaign.
   * @param \Drupal\Core\Session\AccountInterface $recruiter
   *   The recruiter.
   *
   * @return \Drupal\commerce_recruiting\Entity\InvoiceInterface
   *   The created invoice.
   */
  public function createInvoice(CampaignInterface $campaign, AccountInterface $recruiter);

  /**
   * Find invoices.
   *
   * @param \Drupal\Core\Session\AccountInterface $recruiter
   *   The recruiter
   *
   * @return \Drupal\commerce_recruiting\Entity\InvoiceInterface
   *   The invoices.
   */
  public function findInvoices(AccountInterface $recruiter);

}
