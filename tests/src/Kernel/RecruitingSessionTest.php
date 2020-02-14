<?php

namespace Drupal\Tests\commerce_recruitment\Kernel;

/**
 * RecruitingSession.
 *
 * @group commerce_recruitment
 */
class RecruitingSessionTest extends CommerceRecruitingKernelTestBase {

  /**
   * The recruiting session.
   *
   * @var \Drupal\commerce_recruitment\RecruitingSessionInterface
   */
  private $recruitingSession;

  /**
   * Test getRecruiter.
   */
  public function testGetRecruiter() {
    $expected_recruiter = $this->createUser();
    $this->recruitingSession->setRecruiter($expected_recruiter);
    $loaded_recruiter = $this->recruitingSession->getRecruiter();
    $this->assertEqual($expected_recruiter->id(), $loaded_recruiter->id());
  }

  /**
   * Test getRecruitingConfig.
   */
  public function testGetRecruitingConfig() {
    $expected_config = $this->createRecruitmentConfig();
    $this->recruitingSession->setRecruitingConfig($expected_config);
    $loaded_config = $this->recruitingSession->getRecruitingConfig();
    $this->assertEqual($expected_config->id(), $loaded_config->id());
  }

  /**
   * Test setUp.
   */
  protected function setUp() {
    parent::setUp();
    /** @var \Drupal\commerce_recruitment\RecruitingSessionInterface recruitingSession */
    $this->recruitingSession = \Drupal::service('commerce_recruitment.recruiting_session');
  }

}
