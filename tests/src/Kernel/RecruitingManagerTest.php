<?php

namespace Drupal\Tests\commerce_recruiting\Kernel;

use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductVariation;
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

    $order = $this->createOrder($products);

    $recrutings = [];
    foreach ($order->getItems() as $item) {
      $this->assertNotEqual($item->getOrder(), NULL);
      $this->assertTrue($item->getPurchasedEntity() instanceof ProductVariation);
      $recruting = $this->recruitingManager->createRecruiting($item, $recruiter, $recruited, $campaign->getFirstOption(), new Price("10", "USD"));
      $recruting->save();
      $this->assertNotEqual($recruting->getOrder(), NULL);
      $this->assertNotEqual($recruting->getProduct(), NULL);
      $this->assertNotNull($recruting->getProduct());
      $this->assertTrue($recruting->product->entity instanceof ProductVariation, get_class($recruting->product->entity));
      $recrutings[] = $recruting;

    }
    $this->assertEqual(count($recrutings), 2);
    $this->recruitingManager->applyTransitions("accept");
    $items = $this->entityTypeManager->getStorage('commerce_recruiting')->loadByProperties(['state' => 'accepted']);
    $this->assertEqual(count($items), 0);
  }

}
