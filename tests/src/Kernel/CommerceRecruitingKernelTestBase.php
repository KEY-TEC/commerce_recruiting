<?php

namespace Drupal\Tests\commerce_recruitment\Kernel;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_order\Entity\OrderItemType;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_recruitment\Entity\RecruitingConfig;
use Drupal\commerce_recruitment\Entity\RecruitingConfigInterface;
use Drupal\commerce_recruitment\Entity\RecruitingEntity;
use Drupal\Tests\commerce\Kernel\CommerceKernelTestBase;
use Drupal\Tests\commerce_cart\Traits\CartManagerTestTrait;
use Drupal\Tests\commerce_recruitment\Traits\RecruitingEntityCreationTrait;
use Drupal\user\Entity\User;

/**
 * Base kernel test.
 *
 * @group commerce_recruitment
 */
class CommerceRecruitingKernelTestBase extends CommerceKernelTestBase {

  use CartManagerTestTrait;
  use RecruitingEntityCreationTrait;

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
   * The Recruiting manager.
   *
   * @var \Drupal\commerce_recruitment\RecruitingManagerInterface
   */
  protected $recruitingManager;

  /**
   * Creates test order.
   *
   * @param array $products
   *   Each product one product item.
   */
  protected function createTestOrder(array $products = []) {

    $order = Order::create([
      'type' => 'default',
      'state' => 'completed',
      'store_id' => $this->store->id(),
    ]);
    $order->save();
    $items = [];

    /** @var \Drupal\commerce_product\Entity\Product $product */
    foreach ($products as $product) {
      $order_item = OrderItem::create([
        'type' => 'test',
      ]);
      $order_item->setTitle('My order item');
      $this->assertEquals('My order item', $order_item->getTitle());
      $this->assertEquals(1, $order_item->getQuantity());
      $order_item->setQuantity('2');
      $this->assertEquals(2, $order_item->getQuantity());
      $this->assertEquals(NULL, $order_item->getUnitPrice());
      $this->assertFalse($order_item->isUnitPriceOverridden());
      $unit_price = new Price(10, 'USD');
      $order_item->setUnitPrice($unit_price, TRUE);
      $order_item->purchased_entity = $product;
      $order_item->save();
      $items[] = $order_item;
    }

    $order->setItems($items);
    return $order;
  }

  /**
   * Setup commerce shop and products.
   */
  protected function createProduct() {

    $store = $this->store;
    // Add currency...
    // Create some products...
    $product = Product::create([
      'type' => 'default',
      'title' => 'product ',
      'stores' => [$store],
    ]);
    $product->save();
    return $product;
  }

  /**
   * Create an recruiting entity.
   *
   * @return \Drupal\commerce_recruitment\Entity\RecruitingEntityInterface
   *   The recruiting entity.
   */
  protected function createRecruitmentEntity(array $options = [
    'type' => 'default',
    'name' => 'test',
  ]) {
    $recruitment = RecruitingEntity::create($options);
    return $recruitment;
  }

  /**
   * Create an recruiting entity.
   *
   * @return \Drupal\commerce_recruitment\Entity\RecruitingConfigInterface
   *   The recruiting entity.
   */
  protected function createRecruitmentConfig(User $recruiter = NULL, Product $product = NULL, $bonus = 10, $bonus_method = RecruitingConfigInterface::RECRUIT_BONUS_METHOD_FIX) {
    $options = [
      'name' => 'test',
      'status' => 1,
      'bonus' => new Price($bonus, "USD"),
      'bonus_method' => $bonus_method,
    ];
    if ($recruiter != NULL) {
      $options['recruiter'] = ['target_id' => $recruiter->id()];
    }
    if ($product != NULL) {
      $options['products'] = [
        [
          'target_type' => $product->getEntityTypeId(),
          'target_id' => $product->id(),
        ],
      ];
    }
    $config = RecruitingConfig::create($options);
    $config->save();
    return $config;
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
    $this->installEntitySchema('commerce_promotion');
    $this->installEntitySchema('commerce_recruiting_config');
    $this->installEntitySchema('commerce_recruiting');

    $user = $this->createUser();
    $this->user = $this->reloadEntity($user);
    $this->container->get('current_user')->setAccount($user);

    $this->recruitingManager = $this->container->get('commerce_recruitment.manager');

    $this->installCommerceCart();
    // An order item type that doesn't need a purchasable entity.
    OrderItemType::create([
      'id' => 'test',
      'label' => 'Test',
      'orderType' => 'default',
    ])->save();

    // Reset entity type manager otherwise commerce_recruiting not found.
    $this->entityTypeManager = \Drupal::service('entity_type.manager');
  }

  /**
   * Installs sw cart module.
   */
  private function installCommerceRecruiting() {
    $this->enableModules(['commerce_recruitment']);
  }

}
