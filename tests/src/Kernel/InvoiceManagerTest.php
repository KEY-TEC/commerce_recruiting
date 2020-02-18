<?php

namespace Drupal\Tests\commerce_recruiting\Kernel;

use Drupal\Tests\commerce_recruiting\Traits\RecruitingEntityCreationTrait;

/**
 * InvoiceManager.
 *
 * @group commerce_recruiting
 */
class InvoiceManagerTest extends CommerceRecruitingKernelTestBase {

  use RecruitingEntityCreationTrait;

  /**
   * Test testSessionMatch.
   */
  public function testCreateInvoice() {
    $recruiter = $this->createUser();
    $campaign = $this->createCampaign($recruiter);
    $recruited = $this->createUser();
    $products = [];
    $products[] = $this->createProduct();
    $products[] = $this->createProduct();
    $recrutings = $this->createRecrutings($campaign, $recruiter, $recruited, $products);
    $this->assertEqual(count($recrutings), 2);
    $this->recruitingManager->applyTransitions('accept');
    $invoice = $this->invoiceManager->createInvoice($campaign);
    $this->assertEqual(count($invoice->getRecruitings()), 2);
    /** @var \Drupal\commerce_recruiting\Entity\RecruitingInterface $recruiting */
    foreach ($invoice->getRecruitings() as $recruiting) {
      $this->assertEqual($recruiting->getState()->getId(), 'paid_pending');
    }
    $invoice->setState('paid');
    $invoice->save();
    foreach ($invoice->getRecruitings() as $recruiting) {
      $this->assertEqual($recruiting->getState()->getId(), 'paid');
    }
    $this->assertEqual(20.000000, $invoice->getPrice()->getNumber());

  }

}
