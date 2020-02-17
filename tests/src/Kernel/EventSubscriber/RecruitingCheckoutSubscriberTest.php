<?php

namespace Drupal\Tests\commerce_recruiting\EventSubscriber;

use Drupal\commerce_recruiting\RecruitingSession;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Drupal\Tests\commerce_recruiting\Kernel\CommerceRecruitingKernelTestBase;

/**
 * Class RecruitingCheckoutSubscriberTest.
 *
 * @package Drupal\Tests\commerce_recruiting\EventSubscriber
 */
class RecruitingCheckoutSubscriberTest extends CommerceRecruitingKernelTestBase {

  /**
   * Tests onOrderPlace.
   */
  public function testOnOrderPlace() {

    $recruited_product = $this->createProduct();

    $not_recruited_product = $this->createProduct();
    $recruiter = $this->createUser();

    $campaign = $this->createCampaign($recruiter, $recruited_product);

    $option = $campaign->getFirstOption();
    $workflow_prophecy = $this->prophesize(WorkflowTransitionEvent::CLASS);

    $checkout_user = $this->setUpCurrentUser();

    $order = $this->createOrder([
      $recruited_product,
      $not_recruited_product,
    ]);
    $workflow_prophecy->getEntity()->willReturn($order);
    $session_prophecy = $this->prophesize(RecruitingSession::CLASS);
    $session_prophecy->getCampaignOption()->willReturn($option);
    $session_prophecy->getRecruiter()->willReturn($recruiter);
    \Drupal::getContainer()->set('commerce_recruiting.recruiting_session', $session_prophecy->reveal());

    /** @var \Drupal\commerce_recruiting\EventSubscriber\RecruitingCheckoutSubscriber $checkout_subscriber */
    $checkout_subscriber = \Drupal::service('commerce_recruiting.recruiting_checkout_subscriber');
    $checkout_subscriber->onOrderPlace($workflow_prophecy->reveal());
    $recruitings = $this->entityTypeManager->getStorage('commerce_recruiting')->loadByProperties([]);
    $this->assertEqual(count($recruitings), 1);
  }

}
