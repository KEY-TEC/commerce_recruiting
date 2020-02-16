<?php

namespace Drupal\Tests\commerce_recruitment\Kernel\Entity;

use Drupal\Tests\commerce_recruitment\Kernel\CommerceRecruitingKernelTestBase;

/**
 * Class RecruitingEntityTest.
 *
 * @covers \Drupal\commerce_recruitment\Entity\Recruiting
 * @group commerce_recruitment
 */
class RecruitingEntityTest extends CommerceRecruitingKernelTestBase {

  /**
   * Test create.
   */
  public function testCreate() {
    $recruitment = $this->createRecruitmentEntity();
    $this->assertEmpty($recruitment->getBonus());
    $this->assertEqual('test', $recruitment->getName());
  }

}
