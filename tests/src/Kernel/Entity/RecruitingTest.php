<?php

namespace Drupal\Tests\commerce_recruiting\Kernel\Entity;

use Drupal\Tests\commerce_recruiting\Kernel\CommerceRecruitingKernelTestBase;

/**
 * Class RecruitingTest.
 *
 * @covers \Drupal\commerce_recruiting\Entity\Recruiting
 * @group commerce_recruiting
 */
class RecruitingTest extends CommerceRecruitingKernelTestBase {

  /**
   * Test create.
   */
  public function testCreate() {
    $recruiting = $this->createRecruiting();
    $this->assertEmpty($recruiting->getBonus());
    $this->assertEqual('test', $recruiting->getName());
  }

}
