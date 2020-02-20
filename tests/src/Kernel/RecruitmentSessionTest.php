<?php

namespace Drupal\Tests\commerce_recruiting\Kernel;

/**
 * RecruitmentSessionTest.
 *
 * @group commerce_recruiting
 */
class RecruitmentSessionTest extends CommerceRecruitingKernelTestBase {

  /**
   * The recruitment session.
   *
   * @var \Drupal\commerce_recruiting\RecruitmentSessionInterface
   */
  private $recruitmentSession;

  /**
   * Test getRecruiter.
   */
  public function testGetRecruiter() {
    $expected_recruiter = $this->createUser();
    $this->recruitmentSession->setRecruiter($expected_recruiter);
    $loaded_recruiter = $this->recruitmentSession->getRecruiter();
    $this->assertEqual($expected_recruiter->id(), $loaded_recruiter->id());
  }

  /**
   * Test getRecruitmentConfig.
   */
  public function testGetRecruitmentConfig() {
    $expected_campaign = $this->createCampaign();
    $options = $expected_campaign->getOptions();
    $this->assertEqual(count($options), 1);
    $expected_option = current($options);
    $this->recruitmentSession->setCampaignOption($expected_option);
    $loaded_config = $this->recruitmentSession->getCampaignOption();
    $this->assertEqual($expected_option->id(), $loaded_config->id());
  }

  /**
   * Test setUp.
   */
  protected function setUp() {
    parent::setUp();
    /** @var \Drupal\commerce_recruiting\RecruitmentSessionInterface recruitingSession */
    $this->recruitmentSession = \Drupal::service('commerce_recruiting.recruitment_session');
  }

}
