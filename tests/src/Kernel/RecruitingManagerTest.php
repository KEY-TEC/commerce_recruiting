<?php

namespace Drupal\Tests\commerce_recruitment\Kernel;

use Drupal\Tests\commerce_recruitment\Traits\RecruitingEntityCreationTrait;

/**
 * RentalPackageManager.
 *
 * @group sw_subscription
 */
class RecruitingManagerTest extends CommerceRecruitingKernelTestBase {

  use RecruitingEntityCreationTrait;

  /**
   * Test testGetRecruitingUrl.
   */
  public function testGetRecruitingUrl() {
    $config = $this->recruitmentSetup();
    /** @var \Drupal\commerce_recruitment\RecruitingManagerInterface $recruitment_serivce */
    $recruitment_service = \Drupal::service('commerce_recruitment.recruiting');
    $url = $recruitment_service->getRecruitingUrl($config);
    $this->assertNotNull($url);
    $this->assertEqual("http://localhost/friend/Mjsy", $url->toString());
  }

  /**
   * Test testGetRecruitingUrl.
   */
  public function testGetRecruitingCode() {
    $config = $this->recruitmentSetup();
    /** @var \Drupal\commerce_recruitment\RecruitingManagerInterface $recruitment_serivce */
    $recruitment_service = \Drupal::service('commerce_recruitment.recruiting');
    $code = $recruitment_service->getRecruitingCode($config);
    $this->assertNotNull($code);
    $this->assertEqual("Mjsy", $code);
  }

  /**
   * Test testGetRecruitingUrl.
   */
  public function testGetRecruitingInfoFromCode() {
    $config = $this->recruitmentSetup();
    /** @var \Drupal\commerce_recruitment\RecruitingManager $recruitment_manager */
    $recruitment_manager = \Drupal::service('commerce_recruitment.recruiting');
    $code = $recruitment_manager->getRecruitingCode($config);
    $this->assertNotNull($code);
    $info = $recruitment_manager->getRecruitingInfoFromCode($code);
    $this->assertNotNull($info['recruiter']);
    $this->assertNotNull($info['recruiting_config']);
    $this->assertEqual($config->id(), $info['recruiting_config']->id());
  }

}
