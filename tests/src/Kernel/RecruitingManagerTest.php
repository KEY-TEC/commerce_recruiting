<?php

namespace Drupal\Tests\commerce_recruitment\Kernel;

use Drupal\Tests\commerce_recruitment\Traits\RecruitingEntityCreationTrait;

/**
 * RecruitingManager.
 *
 * @group commerce_recruitment
 */
class RecruitingManagerTest extends CommerceRecruitingKernelTestBase {

  use RecruitingEntityCreationTrait;

  /**
   * Test testGetRecruitingUrl.
   */
  public function testGetRecruitingUrl() {
    $config = $this->createRecruitmentConfig($this->drupalCreateUser());
    /** @var \Drupal\commerce_recruitment\RecruitingManagerInterface $recruitment_serivce */
    $recruitment_manger = $this->recruitingManager;
    $url = $recruitment_manger->getRecruitingUrl($config);
    $this->assertNotNull($url);
  }

  /**
   * Test testGetRecruitingUrl.
   */
  public function testGetRecruitingCode() {
    $config = $this->createRecruitmentConfig($this->drupalCreateUser());
    /** @var \Drupal\commerce_recruitment\RecruitingManagerInterface $recruitment_serivce */
    $recruitment_manager = $this->recruitingManager;
    $code = $recruitment_manager->getRecruitingCode($config);
    $this->assertNotNull($code);
  }

  /**
   * Test testGetConfigByProduct.
   */
  public function testGetConfigByProduct() {

    $expected_product = $this->createProduct();
    $expected_config = $this->createRecruitmentConfig(NULL, $expected_product);
    $differnt_product = $this->createProduct();
    $differnt_config = $this->createRecruitmentConfig(NULL, $differnt_product);
    $assigned_recruiter_config = $this->createRecruitmentConfig($this->drupalCreateUser(), $differnt_product);
    $configs = $this->recruitingManager->getConfigByProduct($expected_product, NULL);
    $this->assertEqual(1, count($configs));
    $this->assertEqual($expected_config->id(), $configs[0]->id());
  }

  /**
   * Test testGetRecruitingUrl.
   */
  public function testGetRecruitingInfoFromCode() {
    $config = $this->createRecruitmentConfig($this->drupalCreateUser());
    /** @var \Drupal\commerce_recruitment\RecruitingManager $recruitment_manager */
    $recruitment_manager = $this->recruitingManager;
    $code = $recruitment_manager->getRecruitingCode($config);
    $this->assertNotNull($code);
    $info = $recruitment_manager->getRecruitingInfoFromCode($code);
    $this->assertNotNull($info['recruiter']);
    $this->assertNotNull($info['recruiting_config']);
    $this->assertEqual($config->id(), $info['recruiting_config']->id());
  }

}
