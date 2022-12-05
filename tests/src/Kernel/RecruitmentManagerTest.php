<?php

namespace Drupal\Tests\commerce_recruiting\Kernel;

use Drupal\commerce_price\Price;
use Drupal\commerce_recruiting\Entity\CampaignOptionInterface;
use Drupal\commerce_recruiting\RecruitmentSession;
use Drupal\Tests\commerce_recruiting\Traits\RecruitmentEntityCreationTrait;

// @codingStandardsIgnoreStart
/**
 * RecruitmentManagerTest.
 *
 * @group commerce_recruiting
 */
class RecruitmentManagerTest extends CommerceRecruitingKernelTestBase {

  use RecruitmentEntityCreationTrait;

  /**
   * Test sessionMatch.
   */
  public function testSessionMatch() {
    $recruiter = $this->createUser();
    $product_a = $this->createProduct();
    $product_b = $this->createProduct();
    $order_a = $this->createOrder([$product_a]);
    $order_b = $this->createOrder([$product_b]);
    $order_c = $this->createOrder([$product_a, $product_b]);

    // Test campaign with 'bonus for any option' off.
    $campaign = $this->createCampaign($recruiter, NULL, FALSE, FALSE);
    $option_a = $this->createCampaignOption($product_a);
    $option_b = $this->createCampaignOption($product_b);
    $campaign->addOption($option_a)->addOption($option_b);
    $campaign->save();

    $prophecy = $this->prophesize(RecruitmentSession::CLASS);
    $prophecy->getCampaignOption()->willReturn($campaign->getFirstOption());
    $prophecy->getRecruiter()->willReturn($recruiter);
    \Drupal::getContainer()->set('commerce_recruiting.recruitment_session', $prophecy->reveal());

    $this->recruitmentManager = $this->container->get('commerce_recruiting.recruitment_manager');
    $this->assertCount(1, $this->recruitmentManager->sessionMatch($order_a));
    $this->assertCount(0, $this->recruitmentManager->sessionMatch($order_b));
    $this->assertCount(1, $this->recruitmentManager->sessionMatch($order_c));

    // Test with 'bonus for any option' on.
    $campaign = $this->createCampaign($recruiter, NULL, FALSE);
    $option_a = $this->createCampaignOption($product_a);
    $option_b = $this->createCampaignOption($product_b);
    $campaign->addOption($option_a)->addOption($option_b);
    $campaign->save();

    $prophecy = $this->prophesize(RecruitmentSession::CLASS);
    $prophecy->getCampaignOption()->willReturn($campaign->getFirstOption());
    $prophecy->getRecruiter()->willReturn($recruiter);
    \Drupal::getContainer()->set('commerce_recruiting.recruitment_session', $prophecy->reveal());

    $this->assertCount(1, $this->recruitmentManager->sessionMatch($order_a));
    $this->assertCount(1, $this->recruitmentManager->sessionMatch($order_b));
    $this->assertCount(2, $this->recruitmentManager->sessionMatch($order_c));
  }

  /**
   * Test serializeMatch / deserializeMatch.
   */
  public function testSerializeMatch() {
    $recruiter = $this->createUser();
    $product_a = $this->createProduct();
    $order_a = $this->createOrder([$product_a]);
    $campaign = $this->createCampaign($recruiter, NULL, FALSE, FALSE);
    $option_a = $this->createCampaignOption($product_a);
    $campaign->addOption($option_a);
    $campaign->save();

    $prophecy = $this->prophesize(RecruitmentSession::CLASS);
    $prophecy->getCampaignOption()->willReturn($campaign->getFirstOption());
    // Reload user for later assert match down below.
    $recruiter = $this->entityTypeManager->getStorage('user')->load($recruiter->id());
    $prophecy->getRecruiter()->willReturn($recruiter);
    \Drupal::getContainer()->set('commerce_recruiting.recruitment_session', $prophecy->reveal());

    $this->recruitmentManager = $this->container->get('commerce_recruiting.recruitment_manager');
    $session_match = $this->recruitmentManager->sessionMatch($order_a);
    $this->assertCount(1, $session_match);

    // Serialize.
    $session_match = current($session_match);
    $serialized = $this->recruitmentManager->serializeMatch($session_match);
    foreach ($serialized as $key => $value) {
      switch ($key) {
        case 'bonus':
          $this->assertIsObject($value, 'Bonus stays as object');
          break;

        case 'recruiter':
          $this->assertEquals($recruiter->id(), $value, $key .' converted value to id');
          break;

        default:
          $this->assertEquals('1', $value, $key .' converted value to id');
          break;
      }
    }

    // Deserialize.
    $deserialized = $this->recruitmentManager->deserializeMatch($serialized);
    foreach ($session_match as $key => $value) {
      $this->assertEquals($value, $deserialized[$key], 'Deserialized ' . $key);
    }
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

    $this->assertCount(2, $recruitments);
    $this->recruitmentManager->applyTransitions("accept");
    $items = $this->entityTypeManager->getStorage('commerce_recruitment')->loadByProperties(['state' => 'accepted']);
    $this->assertCount(0, $items);
  }

  /**
   * Test resolveRecruitmentBonus.
   */
  public function testResolveRecruitmentBonus() {
    // Campaign test with quantity multiplicator.
    $campaign = $this->createCampaign(NULL, NULL, FALSE);

    // First product and option to test fix bonus calculation.
    $product_a = $this->createProduct();
    $option_a = $this->createCampaignOption($product_a, '15');
    $campaign->addOption($option_a);
    $order_item_a = $this->createOrderItem($product_a, 2);

    // Second product with second option to test percent calculation.
    $product_b = $this->createProduct(new Price(15, 'USD'));
    $option_b = $this->createCampaignOption($product_b, '10', CampaignOptionInterface::RECRUIT_BONUS_METHOD_PERCENT);
    $campaign->addOption($option_b);
    $order_item_b = $this->createOrderItem($product_b, 2);
    $campaign->save();

    $options = $campaign->getOptions();
    $this->assertCount(2, $options);
    $bonus_a = $this->recruitmentManager->resolveRecruitmentBonus($options[0], $order_item_a);
    // 2 * 15 = 30.
    $this->assertEquals(30, $bonus_a->getNumber());

    $bonus_b = $this->recruitmentManager->resolveRecruitmentBonus($options[1], $order_item_b);
    // 2 * 15 / 10 = 3.
    $this->assertEquals(3, $bonus_b->getNumber());


    // Campaign test without quantity multiplicator.
    $campaign = $this->createCampaign(NULL, NULL, FALSE, FALSE, FALSE);

    // First product and option to test fix bonus calculation.
    $product_a = $this->createProduct();
    $option_a = $this->createCampaignOption($product_a, '10');
    $campaign->addOption($option_a);
    $order_item_a = $this->createOrderItem($product_a, 2);

    // Second product with second option to test percent calculation.
    $product_b = $this->createProduct(new Price(10, 'USD'));
    $option_b = $this->createCampaignOption($product_b, '50', CampaignOptionInterface::RECRUIT_BONUS_METHOD_PERCENT);
    $campaign->addOption($option_b);
    $order_item_b = $this->createOrderItem($product_b, 2);
    $campaign->save();

    $options = $campaign->getOptions();
    $this->assertCount(2, $options);
    $bonus_a = $this->recruitmentManager->resolveRecruitmentBonus($options[0], $order_item_a);
    // Fix bonus 10.
    $this->assertEquals(10, $bonus_a->getNumber());

    $bonus_b = $this->recruitmentManager->resolveRecruitmentBonus($options[1], $order_item_b);
    // 50% of 10.
    $this->assertEquals(5, $bonus_b->getNumber());
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
    $this->assertCount(3, $recruitments2);
    $this->recruitmentManager->applyTransitions("accept");
    $summary = $this->recruitmentManager->getRecruitmentSummaryByCampaign($campaign,'accepted', $recruiter);
    $this->assertCount(3, $summary->getResults());
    $this->assertEquals(30, $summary->getTotalPrice()->getNumber());
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
    $this->assertCount(3, $recruitments);
    $found_recruitments = $this->recruitmentManager->findRecruitmentsByCampaign($campaign, 'created', $recruiter);
    $this->assertCount(3, $found_recruitments);
  }

}
