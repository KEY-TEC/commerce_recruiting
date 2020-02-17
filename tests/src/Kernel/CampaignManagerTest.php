<?php

namespace Drupal\Tests\commerce_recruiting\Kernel;

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
   * Test testGetRecruitingUrl.
   */
  public function testGetRecruitingInfoFromCode() {
    $campaign = $this->createCampaign($this->drupalCreateUser());
    $code = $campaign->getFirstOption()->getCode();
    $this->assertNotNull($code);
    $option = $this->campaignManager->findCampaignOptionFromCode($code);
    $this->assertNotNull($option);
    $this->assertNotNull($option->getCampaign()->getRecruiter());
    $this->assertEqual($campaign->getFirstOption()->id(), $option->id());
  }

}
