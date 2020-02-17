<?php

namespace Drupal\Tests\commerce_recruiting\Kernel\Entity;

use Drupal\Tests\commerce_recruiting\Kernel\CommerceRecruitingKernelTestBase;

/**
 * Class RecruitingTest.
 *
 * @covers \Drupal\commerce_recruiting\Entity\Recruiting
 * @group commerce_recruiting
 */
class RecruitingCampaignTest extends CommerceRecruitingKernelTestBase {

  /**
   * Test create.
   */
  public function testCreate() {
    $recruiting_config = $this->createCampaign();
    $this->assertEqual('test', $recruiting_config->getName());
  }

}
