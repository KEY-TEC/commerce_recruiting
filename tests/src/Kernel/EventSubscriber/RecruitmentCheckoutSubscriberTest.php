<?php

namespace Drupal\Tests\commerce_recruiting\EventSubscriber;

use Drupal\commerce_price\Price;
use Drupal\commerce_recruiting\Code;
use Drupal\commerce_recruiting\RecruitmentSession;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Drupal\Tests\commerce_recruiting\Kernel\CommerceRecruitingKernelTestBase;

/**
 * Test class for the recruitment checkout subscriber.
 *
 * @package Drupal\Tests\commerce_recruiting\EventSubscriber
 */
class RecruitmentCheckoutSubscriberTest extends CommerceRecruitingKernelTestBase {

  /**
   * Tests onOrderPlace.
   *
   * @covers ::onRecruitmentSessionSet
   * @covers ::orderItemsSaveRecruitmentInfo
   * @covers ::onOrderPlace
   */
  public function testOnOrderPlace() {
    $recruited_product = $this->createProduct();
    $not_recruited_product = $this->createProduct();
    $recruiter = $this->createUser();
    $campaign = $this->createCampaign($recruiter, $recruited_product);
    $recruitment_storage = $this->entityTypeManager->getStorage('commerce_recruitment');

    $checkout_user = $this->createUser([], ['view commerce_product']);
    \Drupal::currentUser()->setAccount($checkout_user);
    $cart = $this->createOrder(
      [
        $recruited_product,
        $not_recruited_product,
      ], 'draft', TRUE
    );

    $workflow_prophecy = $this->prophesize(WorkflowTransitionEvent::CLASS);
    $workflow_prophecy->getEntity()->willReturn($cart);
    $workflow_transition_event = $workflow_prophecy->reveal();

    // No recruitment test.
    /** @var \Drupal\commerce_recruiting\EventSubscriber\RecruitmentCheckoutSubscriber $checkout_subscriber */
    $checkout_subscriber = \Drupal::service('commerce_recruiting.recruitment_checkout_subscriber');
    $checkout_subscriber->onOrderPlace($workflow_transition_event);
    $recruitments = $recruitment_storage->loadByProperties([]);
    $this->assertCount(0, $recruitments, 'No recruitment because no session.');

    // Adding recruitment session after products were added.
    $code = Code::createFromCode($campaign->getFirstOption()->getCode() . '--' . $recruiter->id());
    /** @var \Drupal\commerce_recruiting\CampaignManagerInterface $campaign_manager */
    $campaign_manager = \Drupal::getContainer()->get('commerce_recruiting.campaign_manager');
    $campaign_manager->saveRecruitmentSession($code);

    $checkout_subscriber->onOrderPlace($workflow_transition_event);
    $recruitments = $recruitment_storage->loadByProperties([]);
    $this->assertCount(1, $recruitments, 'First recruitment after session was set while product was already in cart.');

    // Adding recruiter product after session was added.
    $new_order_item = $this->createOrderItem($recruited_product);
    $unit_price = new Price(10, 'USD');
    $new_order_item->setUnitPrice($unit_price, TRUE);
    $new_order_item->save();

    /** @var \Drupal\commerce_cart\CartManagerInterface $cart_manager */
    $cart_manager = \Drupal::getContainer()->get('commerce_cart.cart_manager');
    $cart = $this->reloadEntity($cart);
    $cart_manager->emptyCart($cart);
    $cart_manager->addOrderItem($cart, $new_order_item);

    $workflow_prophecy = $this->prophesize(WorkflowTransitionEvent::CLASS);
    $workflow_prophecy->getEntity()->willReturn($cart);
    $workflow_transition_event = $workflow_prophecy->reveal();
    $checkout_subscriber->onOrderPlace($workflow_transition_event);
    $recruitments = $recruitment_storage->loadByProperties([]);
    $this->assertCount(2, $recruitments, 'Second recruitment: Product was added after session was set.');

    // Reset recruitment session for subsequent order place test (re-recruit).
    $session_prophecy = $this->prophesize(RecruitmentSession::CLASS);
    $session_prophecy->getCampaignOption()->willReturn(NULL);
    $session_prophecy->getRecruiter()->willReturn(NULL);
    \Drupal::getContainer()->set('commerce_recruiting.recruitment_session', $session_prophecy->reveal());

    $new_order_item = $this->createOrderItem($recruited_product);
    $new_order_item->setUnitPrice($unit_price, TRUE);
    $new_order_item->save();

    $cart = $this->reloadEntity($cart);
    $cart_manager->emptyCart($cart);
    $cart_manager->addOrderItem($cart, $new_order_item);

    $workflow_prophecy = $this->prophesize(WorkflowTransitionEvent::CLASS);
    $workflow_prophecy->getEntity()->willReturn($cart);
    $workflow_transition_event = $workflow_prophecy->reveal();
    $checkout_subscriber->onOrderPlace($workflow_transition_event);
    $recruitments = $recruitment_storage->loadByProperties([]);
    $this->assertCount(2, $recruitments, 'Same count because no match from session.');

    // Test with auto re recruit option.
    // Note: campaign->set auto re recruit + save doesn't apply for some reason,
    // so create a new campaign with this option on
    // and replace the campaign option of existing recruitment.
    $campaign = $this->createCampaign($recruiter, $recruited_product, TRUE, FALSE, FALSE, TRUE);

    /** @var \Drupal\commerce_recruiting\Entity\RecruitmentInterface $recruitment */
    $recruitment = current($recruitments);
    $recruitment->set('campaign_option', $campaign->getFirstOption());
    $recruitment->save();

    $checkout_subscriber->onOrderPlace($workflow_transition_event);
    $recruitments = $recruitment_storage->loadByProperties([]);
    $this->assertCount(3, $recruitments, 'Third recruitment because of match from active auto re recruit option.');
  }

}
