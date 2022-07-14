<?php

namespace Drupal\Tests\commerce_recruiting\Kernel\Plugin\Commerce\RecruitmentBonusResolver;

use Drupal\commerce_price\Price;
use Drupal\commerce_recruiting\Entity\CampaignOptionInterface;
use Drupal\Tests\commerce_recruiting\Kernel\CommerceRecruitingKernelTestBase;

/**
 * Tests DefaultBonusResolver.
 *
 * @coversDefaultClass \Drupal\commerce_recruiting\Plugin\Commerce\RecruitmentBonusResolver\DefaultBonusResolver
 *
 * @group commerce_recruiting
 */
class DefaultBonusResolverTest extends CommerceRecruitingKernelTestBase {

  /**
   * Tests resolveBonus.
   *
   * @covers ::resolveBonus
   */
  public function testResolveBonus() {
    // Campaign is created with default bonus resolver.
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
    $bonus_resolver = $campaign->getBonusResolver();
    $bonus_a = $bonus_resolver->resolveBonus($options[0], $order_item_a);
    // 2 * 15 = 30.
    $this->assertEquals(30, $bonus_a->getNumber());

    $bonus_b = $bonus_resolver->resolveBonus($options[1], $order_item_b);
    // 2 * 15 / 10 = 3.
    $this->assertEquals(3, $bonus_b->getNumber());
  }

}
