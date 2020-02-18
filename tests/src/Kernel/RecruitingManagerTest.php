<?php

namespace Drupal\Tests\commerce_recruiting\Kernel;

use Drupal\commerce_recruiting\RecruitingSession;
use Drupal\Tests\commerce_recruiting\Traits\RecruitingEntityCreationTrait;

/**
 * RecruitingManager.
 *
 * @group commerce_recruiting
 */
class RecruitingManagerTest extends CommerceRecruitingKernelTestBase {

  use RecruitingEntityCreationTrait;

  /**
   * Test testSessionMatch.
   */
  public function testSessionMatch() {
    $recruiter = $this->createUser();
    $product1 = $this->createProduct();
    $product2 = $this->createProduct();
    $oder = $this->createOrder([$product1]);
    $oder2 = $this->createOrder([$product2]);
    $prophecy = $this->prophesize(RecruitingSession::CLASS);
    $session_config = $this->createCampaign($recruiter, $product1);
    $prophecy->getCampaignOption()->willReturn($session_config->getFirstOption());
    $prophecy->getRecruiter()->willReturn($recruiter);
    \Drupal::getContainer()->set('commerce_recruiting.recruiting_session', $prophecy->reveal());
    $this->recruitingManager = $this->container->get('commerce_recruiting.manager');
    $matches = $this->recruitingManager->sessionMatch($oder);
    $this->assertEqual(count($matches), 1);
    $matches = $this->recruitingManager->sessionMatch($oder2);
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

    $recrutings = $this->createRecrutings($campaign, $recruiter, $recruited, $products, 'draft');

    $this->assertEqual(count($recrutings), 2);
    $this->recruitingManager->applyTransitions("accept");
    $items = $this->entityTypeManager->getStorage('commerce_recruiting')->loadByProperties(['state' => 'accepted']);
    $this->assertEqual(count($items), 0);
  }

  /**
   * Test recruitingSummaryByCampaign.
   */
  public function testRecruitingSummaryByCampaign() {
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

    $this->createRecrutings($campaign, $recruiter, $recruited,
      [$product1, $product2, $product3]
    );
    $recrutings2 = $this->createRecrutings($campaign2, $recruiter, $recruited,
      [$productc21, $productc22, $productc23]
    );
    $this->assertEqual(count($recrutings2), 3);
    $this->recruitingManager->applyTransitions("accept");
    $summary = $this->recruitingManager->recruitingSummaryByCampaign($campaign, 'accepted');
    $this->assertEqual(count($summary->getResults()), 3);
    $this->assertEqual($summary->getTotalPrice()->getNumber(), 30);
  }

  /**
   * Test findRecruitingByCampaign.
   */
  public function testFindRecruitingByCampaign() {
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

    $recrutings = $this->createRecrutings($campaign, $recruiter, $recruited,
      [$product1, $product2, $product3]
    );
    $recrutings2 = $this->createRecrutings($campaign2, $recruiter, $recruited,
      [$productc21, $productc22, $productc23]
    );
    $this->assertEqual(count($recrutings), 3);
    $found_recrutings = $this->recruitingManager->findRecruitingByCampaign($campaign, 'created');
    $this->assertEqual(count($found_recrutings), 3);
  }

}
