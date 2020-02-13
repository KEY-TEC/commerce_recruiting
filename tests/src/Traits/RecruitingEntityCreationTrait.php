<?php

namespace Drupal\Tests\commerce_recruitment\Traits;

use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_product_bundle\Entity\ProductBundle;
use Drupal\commerce_product_bundle\Entity\ProductBundleItem;
use Drupal\commerce_recruitment\Entity\RecruitingConfig;
use Drupal\commerce_recruitment\Entity\RecruitingEntity;
use Drupal\commerce_recruitment\Entity\RecruitingEntityType;
use Drupal\commerce_store\Entity\Store;

/**
 * Provides methods to create recruiting entities.
 *
 * This trait is meant to be used only by test classes.
 */
trait RecruitingEntityCreationTrait {

  use FieldCreationTrait;

  /**
   * User list.
   *
   * @var array
   */
  protected $users = [];

  /**
   * Store list.
   *
   * @var array
   */
  protected $stores = [];

  /**
   * Product list.
   *
   * @var array
   */
  protected $products = [];

  /**
   * Bundle list.
   *
   * @var array
   */
  protected $bundles = [];

  /**
   * Recruiting config list.
   *
   * @var array
   */
  protected $recruitingConfigs = [];

  /**
   * Install required product bundle / order type etc.
   */
  protected function installRecruitingEntity() {
    $recruiting_type = RecruitingEntityType::create([
      'id' => 'default',
    ]);
    $recruiting_type->save();
  }

  /**
   * Install required product bundle / order type etc.
   */
  protected function installRecruitingConfig() {
    $recruiting_type = RecruitingConfig::create([
      'id' => 'default',
    ]);
    $recruiting_type->save();
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
  protected function createRecruitmentConfig(array $options = [
    'type' => 'default',
    'name' => 'test',
  ]) {
    $recruitment = RecruitingConfig::create($options);
    $recruitment->save();
    return $recruitment;
  }

  /**
   * Setup recruitment config.
   *
   * @return \Drupal\commerce_recruitment\Entity\RecruitingConfigInterface[]
   *   The recruitment config.
   */
  protected function recruitmentSetup() {
    $this->shopSetup();
    $this->recruitingConfigSetup();
    return $this->recruitingConfigs;
  }

  /**
   * Setup commerce shop and products.
   */
  protected function shopSetup($users = 1, $stores = 1, $create_products = 3, $create_variations = 2, $create_bundles = 1, $create_bundle_items = 2, array $currencies = ['EUR']) {
    // Create user...
    for ($u = 1; $u <= $users; $u++) {
      $user = $this->createUser();
      $this->users[] = $user;
    }

    // Add currencies...
    $currency_importer = \Drupal::service('commerce_price.currency_importer');
    foreach ($currencies as $currency) {
      $currency_importer->import($currency);
    }

    // Create stores...
    for ($s = 1; $s <= $stores; $s++) {
      if (isset($currencies[$s - 1])) {
        $store_currency = $currencies[$s - 1];
      }
      else {
        $store_currency = $currencies[0];
      }

      $default = FALSE;
      if ($s === 1) {
        $default = TRUE;
      }

      $store = Store::create([
        'name' => 'store ' . $s,
        'type' => 'online',
        'mail' => $s . '@store.test',
        'default_currency' => $store_currency,
        'timezone' => 'UTC',
        'is_default' => $default,
        'address' => [
          'country_code' => 'DE',
          'locality' => 'city',
          'postal_code' => '12345',
          'address_line1' => 'street',
        ]
      ]);
      $store->save();
      $this->stores[] = $store;
      // For each store create...

      // Create some products...
      for ($p = 1; $p <= $create_products; $p++) {
        $variations = [];
        // Product variations...
        for ($v = 0; $v < $create_variations; $v++) {
          /** @var \Drupal\commerce_product\Entity\ProductVariationInterface $variation */
          $variation = ProductVariation::create([
            'type' => 'default',
            'sku' => 'pv' . $s . $p . $v,
            'price' => new Price($p . $v, $store_currency),
          ]);
          $variation->save();
          $variations[] = $variation;
        }

        // The product.
        $product = Product::create([
          'type' => 'default',
          'title' => 'product '. $s . $p,
          'variations' => $variations,
          'stores' => [$store]
        ]);
        $product->save();
        $this->products[] = $product;
      }

      // Create a product bundle with a subset of products...
      for ($b = 1; $b <= $create_bundles; $b++) {
        $bundle_items = [];

        // Create bundle items...
        for ($bi = 0; $bi < $create_bundle_items; $bi++) {
          if (isset($products[$b + $bi - 1])) {
            $bundle_item = ProductBundleItem::create([
              'type' => 'default',
              'title' => 'bundle item '. $bi,
              'product' => $this->products[$b + $bi - 1],
            ]);

            $bundle_item->save();
            $bundle_items[] = $bundle_item;
          }
        }

        // The bundle.
        $bundle = ProductBundle::create([
          'type' => 'default',
          'title' => 'bundle ',
          'bundle_price' => new Price($b . $bi, $store_currency),
          'bundle_items' => $bundle_items,
          'stores' => [$store]
        ]);
        $bundle->save();
        $this->bundles[] = $bundle;
      }
    }
  }

  protected function recruitingConfigSetup($currency = 'EUR') {
    // Create a general recruitment config...
    // Applies for any product ("recruit a friend")
    $recruiting_config = $this->createRecruitmentConfig([
      'name' => 'general',
      'bonus' => new Price(1, $currency),
    ]);
    $this->recruitingConfigs[] = $recruiting_config;

    // Create a product specific recruitment config...
    $recruiting_config = $this->createRecruitmentConfig([
      'name' => 'product',
      'bonus' => new Price(2, $currency),
      'product' => $this->products[0],
    ]);
    $this->recruitingConfigs[] = $recruiting_config;

    // Create a user specific recruitment config...
    // Applies for any product but will benefit this user.
    $recruiting_config = $this->createRecruitmentConfig([
      'name' => 'user',
      'bonus' => new Price(3, $currency),
      'recruiter' => $this->users[0],
    ]);
    $this->recruitingConfigs[] = $recruiting_config;

    // Create a product-user specific recruitment config...
    $recruiting_config = $this->createRecruitmentConfig([
      'name' => 'product_user',
      'bonus' => new Price(4, $currency),
      'product' => $this->products[1],
      'recruiter' => $this->users[0],
    ]);
    $this->recruitingConfigs[] = $recruiting_config;
    return $recruiting_config;

  }

}
