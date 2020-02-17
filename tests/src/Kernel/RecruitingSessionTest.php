<?php

namespace Drupal\Tests\commerce_recruiting\Kernel;

/**
 * RecruitingSessionTest.
 *
 * @group commerce_recruiting
 */
class RecruitingSessionTest extends CommerceRecruitingKernelTestBase {

  /**
   * The recruiting session.
   *
   * @var \Drupal\commerce_recruiting\RecruitingSessionInterface
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
   * Test getCampaignOption.
   */
  public function testGetRecruitingConfig() {
    $expected_campaign = $this->createCampaign();
    $options = $expected_campaign->getOptions();
    $this->assertEqual(count($options), 1);
    $expected_option = current($options);
    $this->recruitingSession->setRecruitingCampaignOption($expected_option);
    $loaded_config = $this->recruitingSession->getCampaignOption();
    $this->assertEqual($expected_option->id(), $loaded_config->id());
  }

  /**
   * Test setUp.
   */
  protected function setUp() {
    parent::setUp();
    /** @var \Drupal\commerce_recruiting\RecruitingSessionInterface recruitingSession */
    $this->recruitingSession = \Drupal::service('commerce_recruiting.recruiting_session');
  }

}
