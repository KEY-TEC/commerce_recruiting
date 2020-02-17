<?php

namespace Drupal\Tests\commerce_recruiting\Kernel;

use Drupal\commerce_recruiting\Code;
use Drupal\Tests\commerce_recruiting\Traits\RecruitingEntityCreationTrait;

/**
 * RecruitingManager.
 *
 * @group commerce_recruiting
 */
class CampaignManagerTest extends CommerceRecruitingKernelTestBase {

  use RecruitingEntityCreationTrait;

  /**
   * Test testGetConfigByProduct.
   */
  public function testFindCampaignOptions() {

    $expected_product = $this->createProduct();
    $expected_campaign = $this->createCampaign(NULL, $expected_product);

    $differnt_product = $this->createProduct();
    $differnt_config = $this->createCampaign(NULL, $differnt_product);
    $assigned_recruiter_campaign = $this->createCampaign($this->drupalCreateUser(), $differnt_product);
    $options = $this->campaignManager->findCampaignOptions(NULL, $expected_product);
    $this->assertEqual(count($options), 1);
    $this->assertEqual($expected_campaign->id(), $options[$expected_campaign->id()]->id());

  }

  /**
   * Test testGetSessionFromCode.
   */
  public function testGetSessionFromCode() {
    $friend_campaign = $this->createCampaign();
    $friend_recruiter = $this->createUser();
    $code_string = $friend_campaign->getFirstOption()->getCode();
    $code = Code::createFromCode($code_string . '--' . $friend_recruiter->id());
    $session = $this->campaignManager->getSessionFromCode($code);
    $this->assertEqual($session->getRecruiter()->id(), $friend_recruiter->id());
    $this->assertEqual($session->getCampaignOption()->id(), $friend_campaign->getFirstOption()->id());

    $recruiter_recruiter = $this->createUser();
    $recruiter_campaign = $this->createCampaign($recruiter_recruiter);
    $code_string = $recruiter_campaign->getFirstOption()->getCode();
    $code = Code::createFromCode($code_string);
    $recruiter_session = $this->campaignManager->getSessionFromCode($code);
    $this->assertEqual($recruiter_session->getRecruiter()->id(), $recruiter_campaign->getRecruiter()->id());
    $this->assertEqual($recruiter_session->getCampaignOption()->id(), $recruiter_campaign->getFirstOption()->id());

  }

  /**
   * Test testGetRecruitingUrl.
   */
  public function testGetRecruitingInfoFromCode() {
    $campaign = $this->createCampaign($this->drupalCreateUser());
    $code_string = $campaign->getFirstOption()->getCode();
    $code = Code::createFromCode($code_string);
    $this->assertNotNull($code);
    $option = $this->campaignManager->findCampaignOptionFromCode($code);
    $this->assertNotNull($option);
    $this->assertNotNull($option->getCampaign()->getRecruiter());
    $this->assertEqual($campaign->getFirstOption()->id(), $option->id());
  }

}
