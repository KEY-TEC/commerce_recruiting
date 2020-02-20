<?php

namespace Drupal\Tests\commerce_recruiting\Kernel;

use Drupal\commerce_recruiting\RecruitmentSession;
use Drupal\Tests\commerce_recruiting\Traits\RecruitmentEntityCreationTrait;

/**
 * RecruitmentManagerTest.
 *
 * @group commerce_recruiting
 */
class RecruitmentManagerTest extends CommerceRecruitingKernelTestBase {

  use RecruitmentEntityCreationTrait;

  /**
   * Test testSessionMatch.
   */
  public function testSessionMatch() {
    $recruiter = $this->createUser();
    $product1 = $this->createProduct();
    $product2 = $this->createProduct();
    $oder = $this->createOrder([$product1]);
    $oder2 = $this->createOrder([$product2]);
    $prophecy = $this->prophesize(RecruitmentSession::CLASS);
    $session_config = $this->createCampaign($recruiter, $product1);
    $prophecy->getCampaignOption()->willReturn($session_config->getFirstOption());
    $prophecy->getRecruiter()->willReturn($recruiter);
    \Drupal::getContainer()->set('commerce_recruiting.recruitment_session', $prophecy->reveal());
    $this->recruitmentManager = $this->container->get('commerce_recruiting.recruitment_manager');
    $matches = $this->recruitmentManager->sessionMatch($oder);
    $this->assertEqual(count($matches), 1);
    $matches = $this->recruitmentManager->sessionMatch($oder2);
    $this->assertEqual(count($matches), 0);
  }

  /**
   * Test applyTransitions.
   */
  public function testApplyTransitions() {

    $recruiter = $this->createUser();
    $campaign = $this->createCampaign($recruiter);
    $recruited = $this->createUser();
    $products = [];
    $products[] = $this->createProduct();
    $products[] = $this->createProduct();

    $recruitments = $this->createRecruitings($campaign, $recruiter, $recruited, $products, 'draft');

    $this->assertEqual(count($recruitments), 2);
    $this->recruitmentManager->applyTransitions("accept");
    $items = $this->entityTypeManager->getStorage('commerce_recruitment')->loadByProperties(['state' => 'accepted']);
    $this->assertEqual(count($items), 0);
  }

  /**
   * Test recruitmentSummaryByCampaign.
   */
  public function testRecruitmentSummaryByCampaign() {
    $recruiter = $this->createUser();
    $product1 = $this->createProduct();
    $product2 = $this->createProduct();
    $product3 = $this->createProduct();
    $campaign = $this->createCampaign($recruiter, $product1);
    $recruited = $this->createUser();
    $campaign2 = $this->createCampaign($recruiter);
    $productc21 = $this->createProduct();
    $productc22 = $this->createProduct();
    $productc23 = $this->createProduct();

    $this->createRecruitings($campaign, $recruiter, $recruited,
      [$product1, $product2, $product3]
    );
    $recruitments2 = $this->createRecruitings($campaign2, $recruiter, $recruited,
      [$productc21, $productc22, $productc23]
    );
    $this->assertEqual(count($recruitments2), 3);
    $this->recruitmentManager->applyTransitions("accept");
    $summary = $this->recruitmentManager->getRecruitmentSummaryByCampaign($campaign, $recruiter, 'accepted');
    $this->assertEqual(count($summary->getResults()), 3);
    $this->assertEqual($summary->getTotalPrice()->getNumber(), 30);
  }

  /**
   * Test findRecruitmentByCampaign.
   */
  public function testFindRecruitmentByCampaign() {
    $recruiter = $this->createUser();
    $product1 = $this->createProduct();
    $product2 = $this->createProduct();
    $product3 = $this->createProduct();
    $campaign = $this->createCampaign($recruiter, $product1);
    $recruited = $this->createUser();
    $campaign2 = $this->createCampaign($recruiter);
    $productc21 = $this->createProduct();
    $productc22 = $this->createProduct();
    $productc23 = $this->createProduct();

    $recruitments = $this->createRecruitings($campaign, $recruiter, $recruited,
      [$product1, $product2, $product3]
    );
    $recruitments2 = $this->createRecruitings($campaign2, $recruiter, $recruited,
      [$productc21, $productc22, $productc23]
    );
    $this->assertEqual(count($recruitments), 3);
    $found_recruitments = $this->recruitmentManager->findRecruitmentsByCampaign($campaign, 'created', $recruiter);
    $this->assertEqual(count($found_recruitments), 3);
  }

}
