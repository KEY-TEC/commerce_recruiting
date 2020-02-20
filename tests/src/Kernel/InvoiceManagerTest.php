<?php

namespace Drupal\Tests\commerce_recruiting\Kernel;

use Drupal\Tests\commerce_recruiting\Traits\RecruitmentEntityCreationTrait;

/**
 * InvoiceManager.
 *
 * @group commerce_recruiting
 */
class InvoiceManagerTest extends CommerceRecruitingKernelTestBase {

  use RecruitmentEntityCreationTrait;

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
    $this->recruitmentManager->applyTransitions('accept');
    $invoice = $this->invoiceManager->createInvoice($campaign);
    $this->assertEqual(count($invoice->getRecruitments()), 2);
    /** @var \Drupal\commerce_recruiting\Entity\RecruitmentInterface $recruitment */
    foreach ($invoice->getRecruitments() as $recruitment) {
      $this->assertEqual($recruitment->getState()->getId(), 'paid_pending');
    }
    $invoice->setState('paid');
    $invoice->save();
    foreach ($invoice->getRecruitments() as $recruitment) {
      $this->assertEqual($recruitment->getState()->getId(), 'paid');
    }
    $this->assertEqual(20.000000, $invoice->getPrice()->getNumber());

  }

}
