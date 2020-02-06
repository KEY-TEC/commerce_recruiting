<?php

namespace Drupal\Tests\commerce_recruitment\Kernel\Entity;

use Drupal\Tests\commerce_recruitment\Kernel\CommerceRecruitingKernelTestBase;

/**
 * Class RecruitingEntityTest.
 *
 * @covers \Drupal\commerce_recruitment\Entity\RecruitingEntity
 * @group commerce_recruitment
 */
class RecruitingEntityTest extends CommerceRecruitingKernelTestBase {

  /**
   * Test create.
   */
  public function testCreate() {
    $recruitment = $this->createRecruitmentEntity();
    $this->assertNull($recruitment->getBonus());
    $this->assertNull($recruitment->getName());
    $this->assertFalse($recruitment->isPaidOut());
  }

}
