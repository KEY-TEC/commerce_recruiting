<?php

namespace Drupal\Tests\commerce_recruiting\Kernel;

use Drupal\commerce_recruiting\Code;
use Drupal\Tests\commerce_recruiting\Traits\RecruitmentEntityCreationTrait;

/**
 * CampaignManager.
 *
 * @group commerce_recruiting
 */
class CampaignManagerTest extends CommerceRecruitingKernelTestBase {

  use RecruitmentEntityCreationTrait;

  /**
   * Test testFindNoRecruiterCampaigns.
   */
  public function testFindNoRecruiterCampaigns() {
    $unspecific_config = $this->createCampaign();

    $expected_product = $this->createProduct();
    $product_specific_config = $this->createCampaign(NULL, $expected_product);

    // No filter test.
    $configs = $this->campaignManager->findNoRecruiterCampaigns();
    $this->assertEqual(count($configs), 2);
    $this->assertEqual($unspecific_config->id(), $configs[$unspecific_config->id()]->id());
    $this->assertEqual($product_specific_config->id(), $configs[$product_specific_config->id()]->id());

    // Product filter test.
    $configs = $this->campaignManager->findNoRecruiterCampaigns($expected_product);
    $this->assertEqual(count($configs), 1);
    $this->assertEqual($product_specific_config->id(), $configs[$product_specific_config->id()]->id());
  }

  /**
   * Test testFindRecruiterCampaigns.
   */
  public function testFindRecruiterCampaigns() {
    $user = $this->drupalCreateUser();
    $recruiter_config = $this->createCampaign($user);

    $differnt_product = $this->createProduct();
    $recruiter_product_config = $this->createCampaign($user, $differnt_product);

    // User filter test.
    $configs = $this->campaignManager->findRecruiterCampaigns($user);
    $this->assertEqual(count($configs), 2);
    $this->assertEqual($recruiter_config->id(), $configs[$recruiter_config->id()]->id());
    $this->assertEqual($recruiter_product_config->id(), $configs[$recruiter_product_config->id()]->id());
  }

  /**
   * Test testGetSessionFromCode.
   */
  public function testGetSessionFromCode() {
    $friend_campaign = $this->createCampaign();
    $friend_recruiter = $this->createUser();
    $code_string = $friend_campaign->getFirstOption()->getCode();
    $code = Code::createFromCode($code_string . '--' . $friend_recruiter->id());
    $session = $this->campaignManager->saveRecruitmentSession($code);
    $this->assertEqual($session->getRecruiter()->id(), $friend_recruiter->id());
    $this->assertEqual($session->getCampaignOption()->id(), $friend_campaign->getFirstOption()->id());

    $recruiter_recruiter = $this->createUser();
    $recruiter_campaign = $this->createCampaign($recruiter_recruiter);
    $code_string = $recruiter_campaign->getFirstOption()->getCode();
    $code = Code::createFromCode($code_string);
    $recruiter_session = $this->campaignManager->saveRecruitmentSession($code);
    $this->assertEqual($recruiter_session->getRecruiter()->id(), $recruiter_campaign->getRecruiter()->id());
    $this->assertEqual($recruiter_session->getCampaignOption()->id(), $recruiter_campaign->getFirstOption()->id());

  }

  /**
   * Test getRecruitmentInfoFromCode.
   */
  public function testGetRecruitmentInfoFromCode() {
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
