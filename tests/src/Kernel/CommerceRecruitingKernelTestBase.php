<?php

namespace Drupal\Tests\commerce_recruiting\Kernel;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_order\Entity\OrderItemType;
use Drupal\commerce_order\Entity\OrderType;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
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
   * @param array $products
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
    $order = Order::create([
      'type' => 'default',
      'state' => $state,
      'store_id' => $this->store->id(),
    ]);
    $order->save();
    $items = [];

    /** @var \Drupal\commerce_product\Entity\Product $product */
    foreach ($products as $product) {
      /** @var \Drupal\commerce_order\Entity\OrderItemInterface $order_item */
      $order_item = OrderItem::create([
        'type' => 'test',
        'purchased_entity' => $product->getDefaultVariation(),
      ]);
      $order_item->save();

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
   * Setup commerce shop and products.
   */
  protected function createProduct() {

    $store = $this->store;
    // Add currency...
    // Create some products...
    /** @var \Drupal\commerce_product\Entity\Product $product */
    $product = Product::create([
      'type' => 'default',
      'title' => 'product ',
      'stores' => [$store],
    ]);

    $variation = ProductVariation::create([
      'type' => 'test',
      'title' => 'My Super Product',
      'status' => TRUE,
    ]);
    $variation->save();
    $product->addVariation($variation);
    $product->save();
    return $product;
  }

  /**
   * Create an recruitment entity.
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
   * Create an campaign entity.
   *
   * @return \Drupal\commerce_recruiting\Entity\CampaignInterface
   *   The campaign entity.
   */
  protected function createCampaign(User $recruiter = NULL, Product $product = NULL, $bonus = 10, $bonus_method = CampaignOptionInterface::RECRUIT_BONUS_METHOD_FIX) {
    $options = [
      'name' => 'test',
      'status' => 1,

    ];
    if ($recruiter != NULL) {
      $options['recruiter'] = ['target_id' => $recruiter->id()];
    }

    $campaign = Campaign::create($options);
    if ($product == NULL) {
      $product = $this->createProduct();
    }
    $option = CampaignOption::create([
      'product' => [
        'target_type' => $product->getEntityTypeId(),
        'target_id' => $product->id(),
      ],
      'status' => 1,
      'bonus' => new Price($bonus, "USD"),
      'bonus_method' => $bonus_method,
    ]);
    $option->save();
    $campaign->addOption($option);
    $campaign->save();
    return $campaign;
  }

  /**
   * Create test recruitments.
   */
  protected function createRecruitings(CampaignInterface $campaign, User $recruiter, User $recruited, $products, $order_state = 'completed') {
    $order = $this->createOrder($products, $order_state);
    foreach ($order->getItems() as $item) {
      $this->assertNotEqual($item->getOrder(), NULL);
      $this->assertTrue($item->getPurchasedEntity() instanceof ProductVariation);
      $recruitment = $this->recruitmentManager->createRecruitment($item, $recruiter, $recruited, $campaign->getFirstOption(), new Price("10", "USD"));
      $recruitment->save();
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
  protected function setUp() {
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
