<?php

namespace Drupal\Tests\commerce_recruitment\Kernel;

use Drupal\commerce_product\Entity\Product;
use Drupal\Tests\commerce\Kernel\CommerceKernelTestBase;
use Drupal\Tests\commerce_cart\Traits\CartManagerTestTrait;
use Drupal\Tests\commerce_recruitment\Traits\RecruitingEntityCreationTrait;

/**
 * RentalPackageManager.
 *
 * @group sw_rental_package
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
   * The Recruting service.
   *
   * @var \Drupal\commerce_recruitment\RecruitingManagerInterface
   */
  protected $recruitingService;

  /**
   * Setup commerce shop and products.
   */
  protected function shopSetup() {

    $store = $this->store;
    // Add currency...
    // Create some products...
    $create_products = 3;
    $create_variations = 2;
    $products = [];
    for ($p = 1; $p <= $create_products; $p++) {
      $product = Product::create([
        'type' => 'default',
        'title' => 'product ' . $p,
        'stores' => [$store],
      ]);
      $product->save();
      $products[] = $product;
    }
    return $products;
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
    $this->installEntitySchema('commerce_recruiting');
    $this->installEntitySchema('commerce_recruiting_config');

    $user = $this->createUser();
    $this->user = $this->reloadEntity($user);
    $this->container->get('current_user')->setAccount($user);

    $this->recruitingService = $this->container->get('commerce_recruitment.recruiting');

    $this->installCommerceCart();
    $this->installRecruitingConfig();
    $this->installRecruitingEntity();

  }

  /**
   * Installs sw cart module.
   */
  private function installCommerceRecruiting() {
    $this->enableModules(['commerce_recruitment']);
  }

}
