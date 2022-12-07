<?php

namespace Drupal\Tests\commerce_recruiting\Kernel;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_order\Entity\OrderItemType;
use Drupal\commerce_order\Entity\OrderType;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_product\Entity\ProductVariationType;
use Drupal\commerce_recruiting\Entity\Campaign;
use Drupal\commerce_recruiting\Entity\CampaignInterface;
use Drupal\commerce_recruiting\Entity\CampaignOptionInterface;
use Drupal\commerce_recruiting\Entity\Recruitment;
use Drupal\commerce_recruiting\Entity\CampaignOption;
use Drupal\Tests\commerce\Kernel\CommerceKernelTestBase;
use Drupal\Tests\commerce_cart\Traits\CartManagerTestTrait;
use Drupal\Tests\commerce_recruiting\Traits\RecruitmentEntityCreationTrait;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;

/**
 * Base kernel test.
 *
 * @group commerce_recruiting
 */
class CommerceRecruitingKernelTestBase extends CommerceKernelTestBase {

  use CartManagerTestTrait;
  use RecruitmentEntityCreationTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'commerce_order',
    'commerce_product',
    'commerce_promotion',
    'entity_reference_revisions',
    'dynamic_entity_reference',
    'profile',
    'state_machine',
    'link',
  ];

  /**
   * The reward manager.
   *
   * @var \Drupal\commerce_recruiting\RewardManagerInterface
   */
  protected $rewardManager;

  /**
   * The campaign manager.
   *
   * @var \Drupal\commerce_recruiting\CampaignManagerInterface
   */
  protected $campaignManager;

  /**
   * The recruitment manager.
   *
   * @var \Drupal\commerce_recruiting\RecruitmentManagerInterface
   */
  protected $recruitmentManager;

  /**
   * Creates test order.
   *
   * @param \Drupal\commerce_product\Entity\ProductInterface[] $products
   *   Each product one product item.
   * @param string $state
   *   The order state.
   *
   * @return \Drupal\commerce_order\Entity\Order
   *   The order.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createOrder(array $products = [], $state = 'completed') {
    $options = [
      'type' => 'default',
      'state' => $state,
      'store_id' => $this->store->id(),
    ];

    $order = Order::create($options);
    $order->save();

    $items = [];
    foreach ($products as $product) {
      /** @var \Drupal\commerce_order\Entity\OrderItemInterface $order_item */
      $order_item = $this->createOrderItem($product);

      $product_variation = $order_item->getPurchasedEntity();
      $this->assertNotEmpty($product_variation, 'Purchased entity is not empty.');
      $this->assertTrue($product_variation instanceof ProductVariation, 'Purchased entity is product variation.');
      $order_item->setTitle('My order item');
      $this->assertEquals('My order item', $order_item->getTitle());
      $this->assertEquals(1, $order_item->getQuantity());
      $order_item->setQuantity('2');
      $this->assertEquals(2, $order_item->getQuantity());
      $this->assertEquals(NULL, $order_item->getUnitPrice());
      $this->assertFalse($order_item->isUnitPriceOverridden());
      $unit_price = new Price(10, 'USD');
      $order_item->setUnitPrice($unit_price, TRUE);
      $order_item->save();

      $items[] = $order_item;
    }

    $order->setItems($items);

    $order->save();
    return $order;
  }

  /**
   * Creates an order item.
   *
   * @param \Drupal\commerce_product\Entity\ProductInterface $product
   *   The product.
   * @param int $quantity
   *   The order item quantity.
   *
   * @return \Drupal\commerce_order\Entity\OrderItemInterface
   *   The order item.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createOrderItem(ProductInterface $product, $quantity = 1) {
    $order_item = OrderItem::create([
      'type' => 'test',
      'purchased_entity' => $product->getDefaultVariation(),
      'quantity' => $quantity,
      'unit_price' => $product->getDefaultVariation()->getPrice(),
    ]);
    $order_item->save();
    return $order_item;
  }

  /**
   * Setup commerce shop and products.
   *
   * @param \Drupal\commerce_price\Price $price
   *   The product price.
   */
  protected function createProduct(Price $price = NULL) {
    $store = $this->store;
    /** @var \Drupal\commerce_product\Entity\Product $product */
    $product = Product::create([
      'type' => 'default',
      'title' => 'Test product',
      'stores' => [$store],
    ]);

    $variation = ProductVariation::create([
      'type' => 'test',
      'title' => 'My Super Product',
      'status' => TRUE,
    ]);
    if ($price) {
      $variation->setPrice($price);
    }
    $variation->save();
    $product->addVariation($variation);
    $product->save();
    $default_variation = $product->getDefaultVariation();
    $this->assertEquals($default_variation->id(), $variation->id());
    return $product;
  }

  /**
   * Create a recruitment entity.
   *
   * @return \Drupal\commerce_recruiting\Entity\RecruitmentInterface
   *   The recruitment entity.
   */
  protected function createRecruitment(array $options = [
    'type' => 'default',
    'name' => 'test',
  ]) {
    $recruitment = Recruitment::create($options);
    return $recruitment;
  }

  /**
   * Create a campaign entity.
   *
   * Comes with one campaign option, since we usually need one.
   *
   * @param \Drupal\user\UserInterface|null $recruiter
   *   The campaign owner.
   * @param \Drupal\commerce_product\Entity\ProductInterface|null $product
   *   A product for the campaign option.
   * @param bool $create_option
   *   Also create and add a campaign option.
   * @param bool $bonus_any_option
   *   If any option can apply.
   * @param bool $bonus_quantity_multiplication
   *   If order quantity multiplicator can apply.
   * @param bool $auto_re_recruit
   *   Use auto re recruit option.
   * @param array $recruitment_bonus_resolver
   *   Bonus resolver plugin. Leave empty for default.
   * @param int $bonus
   *   The bonus amount.
   * @param string $bonus_method
   *   The campaign option bonus method.
   *
   * @return \Drupal\commerce_recruiting\Entity\CampaignInterface
   *   The campaign entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createCampaign(UserInterface $recruiter = NULL, ProductInterface $product = NULL, bool $create_option = TRUE, bool $bonus_any_option = TRUE, bool $bonus_quantity_multiplication = TRUE, bool $auto_re_recruit = FALSE, array $recruitment_bonus_resolver = [], int $bonus = 10, string $bonus_method = CampaignOptionInterface::RECRUIT_BONUS_METHOD_FIX) {
    $options = [
      'name' => 'test',
      'status' => 1,
      'bonus_any_option' => $bonus_any_option,
      'auto_re_recruit' => $auto_re_recruit,
    ];

    if ($recruiter != NULL) {
      $options['recruiter'] = ['target_id' => $recruiter->id()];
    }
    if (empty($recruitment_bonus_resolver)) {
      $recruitment_bonus_resolver = [
        'target_plugin_id' => 'default_bonus_resolver',
        'target_plugin_configuration' => [
          'bonus_quantity_multiplication' => $bonus_quantity_multiplication,
        ],
      ];
    }
    $options['recruitment_bonus_resolver'] = $recruitment_bonus_resolver;

    $campaign = Campaign::create($options);
    if ($create_option) {
      if ($product == NULL) {
        $product = $this->createProduct();
      }
      $option = $this->createCampaignOption($product, $bonus, $bonus_method);
      $campaign->addOption($option);
    }

    $campaign->save();
    return $campaign;
  }

  /**
   * Creates a campaign option entity, if you need another.
   *
   * @param \Drupal\commerce_product\Entity\ProductInterface $product
   *   The product for this option.
   * @param int $bonus
   *   The bonus. Used either as number or percentage, depending on the method.
   * @param string $bonus_method
   *   The bonus calculation method.
   *
   * @return \Drupal\commerce_recruiting\Entity\CampaignOptionInterface
   *   The campaign option.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createCampaignOption(ProductInterface $product, int $bonus = 10, string $bonus_method = CampaignOptionInterface::RECRUIT_BONUS_METHOD_FIX) {
    $option = CampaignOption::create([
      'product' => [
        'target_type' => $product->getEntityTypeId(),
        'target_id' => $product->id(),
      ],
      'status' => 1,
      'bonus_method' => $bonus_method,
    ]);

    switch ($option->getBonusMethod()) {
      case CampaignOptionInterface::RECRUIT_BONUS_METHOD_FIX:
        $option->setBonus(new Price($bonus, 'USD'));
        break;

      case CampaignOptionInterface::RECRUIT_BONUS_METHOD_PERCENT:
        $option->setBonusPercent($bonus);
        break;

    }

    $option->save();
    return $option;
  }

  /**
   * Create test recruitments.
   */
  protected function createRecruitings(CampaignInterface $campaign, User $recruiter, User $recruited, OrderInterface $order) {
    foreach ($order->getItems() as $item) {
      $this->assertNotEqual($item->getOrder(), NULL);
      $this->assertTrue($item->getPurchasedEntity() instanceof ProductVariation);
      $recruitment = $this->recruitmentManager->createRecruitment($item, $recruiter, $recruited, $campaign->getFirstOption(), new Price("10", "USD"));
      $recruitment->save();
      // Reload.
      $recruitment = $this->entityTypeManager->getStorage('commerce_recruitment')->load($recruitment->id());
      $this->assertNotEqual($recruitment->getOrder(), NULL);
      $this->assertNotEqual($recruitment->getProduct(), NULL);
      $this->assertNotNull($recruitment->getProduct());
      $this->assertTrue($recruitment->product->entity instanceof ProductVariation, get_class($recruitment->product->entity));
      $recruitments[] = $recruitment;
    }
    return $recruitments;
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installCommerceRecruiting();
    $this->installEntitySchema('profile');
    $this->installEntitySchema('commerce_order');
    $this->installEntitySchema('commerce_order_item');
    $this->installEntitySchema('commerce_product');
    $this->installEntitySchema('commerce_product_variation');
    $this->installEntitySchema('commerce_promotion');
    $this->installEntitySchema('commerce_recruitment_campaign');
    $this->installEntitySchema('commerce_recruitment_camp_option');
    $this->installEntitySchema('commerce_recruitment');
    $this->installEntitySchema('commerce_recruitment_reward');

    $user = $this->createUser();
    $this->user = $this->reloadEntity($user);
    $this->container->get('current_user')->setAccount($user);

    $this->recruitmentManager = $this->container->get('commerce_recruiting.recruitment_manager');
    $this->campaignManager = $this->container->get('commerce_recruiting.campaign_manager');
    $this->rewardManager = \Drupal::service('commerce_recruiting.reward_manager');
    $this->installCommerceCart();

    $order_type = OrderType::create([
      'id' => 'default',
      'label' => 'Test',
      'orderType' => 'default',
      'workflow' => 'order_default',
    ])->save();

    // An order item type that doesn't need a purchasable entity.
    $order_item_type = OrderItemType::create([
      'id' => 'test',
      'label' => 'Test',
      'orderType' => 'default',
      'purchasableEntityType' => 'commerce_product_variation',
    ])->save();

    ProductVariationType::create([
      'id' => 'test',
      'label' => 'Test',
      'orderItemType' => 'default',
    ])->save();

    // Reset entity type manager otherwise commerce_recruiting not found.
    $this->entityTypeManager = \Drupal::service('entity_type.manager');
  }

  /**
   * Installs sw cart module.
   */
  private function installCommerceRecruiting() {
    $this->enableModules(['commerce_recruiting']);
  }

}
