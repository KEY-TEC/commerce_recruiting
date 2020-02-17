<?php

namespace Drupal\Tests\commerce_recruiting\Kernel;

use Drupal\Tests\commerce_recruiting\Traits\RecruitingEntityCreationTrait;

/**
 * campaignManager.
 *
 * @group commerce_recruiting
 */
class CampaignManagerTest extends CommerceRecruitingKernelTestBase {

  use RecruitingEntityCreationTrait;

  /**
   * Test testGetConfigByProduct.
   */
  public function testFindCampaigns() {
    $unspecific_config = $this->createCampaign(NULL, NULL);

    $expected_product = $this->createProduct();
    $product_specific_config = $this->createCampaign(NULL, $expected_product);

    $user = $this->drupalCreateUser();
    $recruiter_config = $this->createCampaign($user, NULL);

    $differnt_product = $this->createProduct();
    $recruiter_product_config = $this->createCampaign($user, $differnt_product);

    // No filter test.
    $configs = $this->campaignManager->findCampaigns();
    $this->assertEqual(count($configs), 1);
    $this->assertEqual($unspecific_config->id(), $configs[$unspecific_config->id()]->id());

    // Product filter test.
    $configs = $this->campaignManager->findCampaigns(NULL, $expected_product);
    $this->assertEqual(count($configs), 1);
    $this->assertEqual($product_specific_config->id(), $configs[$product_specific_config->id()]->id());

    // User filter test.
    $configs = $this->campaignManager->findCampaigns($user);
    $this->assertEqual(count($configs), 2);
    $this->assertEqual($recruiter_config->id(), $configs[$recruiter_config->id()]->id());
    $this->assertEqual($recruiter_product_config->id(), $configs[$recruiter_product_config->id()]->id());

    // User + product filter test.
    $configs = $this->campaignManager->findCampaigns($user, $differnt_product);
    $this->assertEqual(count($configs), 1);
    $this->assertEqual($recruiter_product_config->id(), $configs[$recruiter_product_config->id()]->id());
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
