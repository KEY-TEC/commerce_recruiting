<?php

namespace Drupal\commerce_recruiting\EventSubscriber;

use Drupal\commerce_recruiting\RecruitmentManagerInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Session\AccountProxy;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Drupal\user\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class RecruitmentCheckoutSubscriber.
 */
class RecruitmentCheckoutSubscriber implements EventSubscriberInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The recruitment manager.
   *
   * @var \Drupal\commerce_recruiting\RecruitmentManagerInterface
   */
  private $recruitmentManager;

  /**
   * Constructs a new RecruitmentCheckoutSubscriber object.
   *
   * @param \Drupal\Core\Session\AccountProxy $current_user
   *   The current user.
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   The messenger.
   * @param \Drupal\commerce_recruiting\RecruitmentManagerInterface $recruitment_manager
   *   The recruitment manager.
   */
  public function __construct(AccountProxy $current_user, Messenger $messenger, RecruitmentManagerInterface $recruitment_manager) {
    $this->currentUser = $current_user;
    $this->messenger = $messenger;
    $this->recruitmentManager = $recruitment_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events['commerce_order.place.post_transition'] = ['onOrderPlace'];
    return $events;
  }

  /**
   * Event handler on order checkout.
   *
   * Creates a recruitment entity and references it in the order
   * if recruitment data is available in the session.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The transition event.
   */
  public function onOrderPlace(WorkflowTransitionEvent $event) {
    /** @var \Drupal\commerce_order\Entity\Order $order */
    $order = $event->getEntity();
    $matches = $this->recruitmentManager->sessionMatch($order);
    $user = User::load($this->currentUser->id());
    if (empty($matches)) {
      // No session set.
      return;
    }

    foreach ($matches as $product_id => $match) {
      $recruitment = $this->recruitmentManager->createRecruitment($match['order_item'], $match['recruiter'], $user, $match['campaign_option'], $match['bonus']);
      $recruitment->save();
    }
  }

}
