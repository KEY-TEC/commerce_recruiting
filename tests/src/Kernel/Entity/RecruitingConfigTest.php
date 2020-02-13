<?php

namespace Drupal\Tests\commerce_recruitment\Kernel\Entity;

use Drupal\Tests\commerce_recruitment\Kernel\CommerceRecruitingKernelTestBase;

/**
 * Class RecruitingEntityTest.
 *
 * @covers \Drupal\commerce_recruitment\Entity\RecruitingEntity
 * @group commerce_recruitment
 */
class RecruitingConfigTest extends CommerceRecruitingKernelTestBase {

  /**
   * Test create.
   */
  public function testCreate() {
    $recruitment_config = $this->createRecruitmentConfig();
    $this->assertEmpty($recruitment_config->getName());
  }

}
