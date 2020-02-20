<?php

namespace Drupal\Tests\commerce_recruiting\Kernel\Entity;

use Drupal\Tests\commerce_recruiting\Kernel\CommerceRecruitingKernelTestBase;

/**
 * Class RecruitmentCampaignTest.
 *
 * @covers \Drupal\commerce_recruiting\Entity\Recruitment
 * @group commerce_recruiting
 */
class RecruitmentCampaignTest extends CommerceRecruitingKernelTestBase {

  /**
   * Test create.
   */
  public function testCreate() {
    $recruitment_config = $this->createCampaign();
    $this->assertEqual('test', $recruitment_config->getName());
  }

}
