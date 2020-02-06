<?php

namespace Drupal\Tests\commerce_recruitment\Kernel;

use Drupal\commerce_order\Entity\OrderType;
use Drupal\commerce_recruitment\Entity\RecruitingEntityType;
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
    'entity_reference_revisions',
    'profile',
    'state_machine',
  ];

  /**
   * @var \Drupal\commerce_recruitment\RecruitingServiceInterface
   */
  protected $recruitingService;

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
    $this->installEntitySchema('recruiting');

    $user = $this->createUser();
    $this->user = $this->reloadEntity($user);
    $this->container->get('current_user')->setAccount($user);

    $this->recruitingService = $this->container->get('commerce_recruitment.recruiting');

    $this->installCommerceCart();
    $this->installRecruitingEntity();
  }

  /**
   * Installs sw cart module.
   */
  private function installCommerceRecruiting() {
    $this->enableModules(['commerce_recruitment']);
  }

}
