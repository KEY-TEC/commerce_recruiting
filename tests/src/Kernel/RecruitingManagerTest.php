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

  public function testApplyTransitions() {

    $recruiter = $this->createUser();
    $products[] = $this->createProduct();
    $products[] = $this->createProduct();

    $order = $this->createOrder($products);
    foreach ($order->get) {

    }
    $oder2 = $this->createOrder([$product2]);
    $prophecy = $this->prophesize(RecruitingSession::CLASS);
    $session_config = $this->createCampaign($recruiter, $product1);
  }

}
