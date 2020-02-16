<?php

namespace Drupal\Tests\commerce_recruitment\Kernel;

use Drupal\commerce_recruitment\RecruitingSession;
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
  public function testFindRecruitingConfig() {

    $expected_product = $this->createProduct();
    $expected_config = $this->createRecruitmentConfig(NULL, $expected_product);

    $differnt_product = $this->createProduct();
    $differnt_config = $this->createRecruitmentConfig(NULL, $differnt_product);
    $assigned_recruiter_config = $this->createRecruitmentConfig($this->drupalCreateUser(), $differnt_product);
    $configs = $this->recruitingManager->findRecruitingConfig(NULL, $expected_product);
    $this->assertEqual(count($configs), 1);
    $this->assertEqual($expected_config->id(), $configs[$expected_config->id()]->id());

    $configs = $this->recruitingManager->findRecruitingConfig(NULL, $expected_product);
  }

  /**
   * Test testSessionMatch.
   */
  public function testSessionMatch() {
    $recruiter = $this->createUser();
    $product1 = $this->createProduct();
    $product2 = $this->createProduct();
    $oder = $this->createTestOrder([$product1]);
    $oder2 = $this->createTestOrder([$product2]);
    $prophecy = $this->prophesize(RecruitingSession::CLASS);
    $session_config = $this->createRecruitmentConfig($recruiter, $product1);
    $prophecy->getRecruitingConfig()->willReturn($session_config);
    \Drupal::getContainer()->set('commerce_recruitment.recruiting_session', $prophecy->reveal());
    $matches = $this->recruitingManager->sessionMatch($oder);
    $this->assertEqual(count($matches), 1);
    $matches = $this->recruitingManager->sessionMatch($oder2);
    $this->assertEqual(count($matches), 0);
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
