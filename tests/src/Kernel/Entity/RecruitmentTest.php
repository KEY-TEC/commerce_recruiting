<?php

namespace Drupal\Tests\commerce_recruiting\Kernel\Entity;

use Drupal\Tests\commerce_recruiting\Kernel\CommerceRecruitingKernelTestBase;

/**
 * Class RecruitmentTest.
 *
 * @covers \Drupal\commerce_recruiting\Entity\Recruitment
 * @group commerce_recruiting
 */
class RecruitmentTest extends CommerceRecruitingKernelTestBase {

  /**
   * Test create.
   */
  public function testCreate() {
    $recruitment = $this->createRecruitment();
    $this->assertEmpty($recruitment->getBonus());
    $this->assertEqual('test', $recruitment->getName());
  }

}
