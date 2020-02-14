<?php

namespace Drupal\Tests\commerce_recruitment\EventSubscriber;

use Drupal\Tests\commerce_recruitment\Kernel\CommerceRecruitingKernelTestBase;

/**
 * Class RecruitingCheckoutSubscriberTest.
 *
 * @package Drupal\Tests\commerce_recruitment\EventSubscriber
 */
class RecruitingCheckoutSubscriberTest extends CommerceRecruitingKernelTestBase {

  /**
   * Tests onOrderPlace.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testOnOrderPlace() {
    $recruiting_configs = $this->createRecruitmentConfig();
    /** @var \Drupal\commerce_recruitment\EventSubscriber\RecruitingCheckoutSubscriber $service */
    $checkout_subscriber = \Drupal::service('commerce_recruitment.recruiting.checkout');
    $checkout_user = $this->setUpCurrentUser();
    $this->assertEqual($checkout_user->id(), \Drupal::currentUser()->id());
  }

}
