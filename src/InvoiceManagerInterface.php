<?php

namespace Drupal\commerce_recruiting;

use Drupal\commerce_recruiting\Entity\CampaignInterface;

/**
 * Interface InvoiceManagerInterface.
 */
interface InvoiceManagerInterface {

  /**
   * Create a invoice for given campaign.
   *
   * @param \Drupal\commerce_recruiting\Entity\CampaignInterface $campaign
   *   The campaign.
   *
   * @return \Drupal\commerce_recruiting\Entity\InvoiceInterface
   *   The created invoice.
   */
  public function createInvoice(CampaignInterface $campaign);

}
