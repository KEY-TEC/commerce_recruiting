<?php

namespace Drupal\Tests\commerce_recruitment\EventSubscriber;

use Drupal\commerce_recruitment\RecruitingSession;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Drupal\Tests\commerce_recruitment\Kernel\CommerceRecruitingKernelTestBase;

/**
 * Class RecruitingCheckoutSubscriberTest.
 *
 * @package Drupal\Tests\commerce_recruitment\EventSubscriber
 */
class RecruitingCheckoutSubscriberTest extends CommerceRecruitingKernelTestBase {

  /**
   * Tests onOrderPlace.
   */
  public function testOnOrderPlace() {

    $recruited_product = $this->createProduct();

    $not_recruited_product = $this->createProduct();
    $recruiter = $this->createUser();

    $recruiting_config = $this->createRecruitmentConfig($recruiter, $recruited_product);

    $workflow_prophecy = $this->prophesize(WorkflowTransitionEvent::CLASS);

    $checkout_user = $this->setUpCurrentUser();

    $order = $this->createTestOrder([
      $recruited_product,
      $not_recruited_product,
    ]);
    $workflow_prophecy->getEntity()->willReturn($order);
    $session_prophecy = $this->prophesize(RecruitingSession::CLASS);
    $session_prophecy->getRecruitingConfig()->willReturn($recruiting_config);
    $session_prophecy->getRecruiter()->willReturn($recruiter);
    \Drupal::getContainer()->set('commerce_recruitment.recruiting_session', $session_prophecy->reveal());

    /** @var \Drupal\commerce_recruitment\EventSubscriber\RecruitingCheckoutSubscriber $checkout_subscriber */
    $checkout_subscriber = \Drupal::service('commerce_recruitment.recruiting_checkout_subscriber');
    $checkout_subscriber->onOrderPlace($workflow_prophecy->reveal());
    $recruitings = $this->entityTypeManager->getStorage('commerce_recruiting')->loadByProperties([]);
    $this->assertEqual(count($recruitings), 1);
  }

}
