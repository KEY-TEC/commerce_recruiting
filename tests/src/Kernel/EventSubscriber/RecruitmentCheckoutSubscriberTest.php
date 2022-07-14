<?php

namespace Drupal\Tests\commerce_recruiting\EventSubscriber;

use Drupal\commerce_recruiting\RecruitmentSession;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Drupal\Tests\commerce_recruiting\Kernel\CommerceRecruitingKernelTestBase;

/**
 * Class RecruitmentCheckoutSubscriberTest.
 *
 * @package Drupal\Tests\commerce_recruiting\EventSubscriber
 */
class RecruitmentCheckoutSubscriberTest extends CommerceRecruitingKernelTestBase {

  /**
   * Tests onOrderPlace.
   */
  public function testOnOrderPlace() {
    $recruited_product = $this->createProduct();
    $not_recruited_product = $this->createProduct();
    $recruiter = $this->createUser();
    $campaign = $this->createCampaign($recruiter, $recruited_product);
    $recruitment_storage = $this->entityTypeManager->getStorage('commerce_recruitment');

    $checkout_user = $this->createUser([], ['view commerce_product']);
    \Drupal::currentUser()->setAccount($checkout_user);
    $order = $this->createOrder([
      $recruited_product,
      $not_recruited_product,
    ]);

    $workflow_prophecy = $this->prophesize(WorkflowTransitionEvent::CLASS);
    $workflow_prophecy->getEntity()->willReturn($order);
    $workflow_transition_event = $workflow_prophecy->reveal();

    // No recruitment test.
    /** @var \Drupal\commerce_recruiting\EventSubscriber\RecruitmentCheckoutSubscriber $checkout_subscriber */
    $checkout_subscriber = \Drupal::service('commerce_recruiting.recruitment_checkout_subscriber');
    $checkout_subscriber->onOrderPlace($workflow_transition_event);
    $recruitments = $recruitment_storage->loadByProperties([]);
    $this->assertCount(0, $recruitments);

    // Adding recruitment session.
    $session_prophecy = $this->prophesize(RecruitmentSession::CLASS);
    $session_prophecy->getCampaignOption()->willReturn($campaign->getFirstOption());
    $session_prophecy->getRecruiter()->willReturn($recruiter);
    \Drupal::getContainer()->set('commerce_recruiting.recruitment_session', $session_prophecy->reveal());

    $checkout_subscriber->onOrderPlace($workflow_transition_event);
    $recruitments = $recruitment_storage->loadByProperties([]);
    $this->assertCount(1, $recruitments);

    // Test with auto re recruit option.
    // Note: campaign->set auto re recruit + save doesn't apply for some reason,
    // so create a new campaign with this option on
    // and replace the campaign option of existing recruitment.
    $campaign = $this->createCampaign($recruiter, $recruited_product, TRUE, FALSE, FALSE, TRUE);

    /** @var \Drupal\commerce_recruiting\Entity\RecruitmentInterface $recruitment */
    $recruitment = current($recruitments);
    $recruitment->set('campaign_option', $campaign->getFirstOption());
    $recruitment->save();

    // Reset recruitment session for subsequent order place test (re-recruit).
    $session_prophecy->getCampaignOption()->willReturn(NULL);
    $session_prophecy->getRecruiter()->willReturn(NULL);
    \Drupal::getContainer()->set('commerce_recruiting.recruitment_session', $session_prophecy->reveal());

    $checkout_subscriber->onOrderPlace($workflow_transition_event);
    $recruitments = $recruitment_storage->loadByProperties([]);
    $this->assertCount(2, $recruitments);
  }

}
