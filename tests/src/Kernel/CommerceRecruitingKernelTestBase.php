<?php

namespace Drupal\Tests\commerce_recruitment\Kernel;

use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_recruitment\Entity\RecruitingConfig;
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
  protected function createRecruitmentConfig(User $recruiter = NULL, Product $product = NULL) {
    $options = [
      'name' => 'test',
    ];
    if ($recruiter != NULL) {
      $options['recruiter'] = ['target_id' => $recruiter->id()];
    }
    if ($product != NULL) {
      $options['product'] = [
        'target_type' => 'product',
        'target_id' => $product->id(),
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
    $this->installEntitySchema('commerce_recruiting');
    $this->installEntitySchema('commerce_recruiting_config');

    $user = $this->createUser();
    $this->user = $this->reloadEntity($user);
    $this->container->get('current_user')->setAccount($user);

    $this->recruitingManager = $this->container->get('commerce_recruitment.manager');

    $this->installCommerceCart();
  }

  /**
   * Installs sw cart module.
   */
  private function installCommerceRecruiting() {
    $this->enableModules(['commerce_recruitment']);
  }

}
