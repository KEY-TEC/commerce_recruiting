<?php

namespace Drupal\commerce_recruitment\EventSubscriber;

use Drupal\commerce_recruitment\RecruitingManagerInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Session\AccountProxy;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Drupal\user\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class RecruitingCheckoutSubscriber.
 */
class RecruitingCheckoutSubscriber implements EventSubscriberInterface {

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
   * The recruting manager.
   *
   * @var \Drupal\commerce_recruitment\RecruitingManagerInterface
   */
  private $recruitingManager;

  /**
   * Constructs a new RecruitingCheckoutSubscriber object.
   *
   * @param \Drupal\Core\Session\AccountProxy $current_user
   *   The current user.
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   The messenger.
   * @param \Drupal\commerce_recruitment\RecruitingManagerInterface $recruiting_manager
   *   The manager.
   */
  public function __construct(AccountProxy $current_user, Messenger $messenger, RecruitingManagerInterface $recruiting_manager) {
    $this->currentUser = $current_user;
    $this->messenger = $messenger;
    $this->recruitingManager = $recruiting_manager;
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
   * Creates a recruiting entity and references it in the order
   * if recruiting data is available in the session.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The transition event.
   */
  public function onOrderPlace(WorkflowTransitionEvent $event) {
    /** @var \Drupal\commerce_order\Entity\Order $order */
    $order = $event->getEntity();
    $matches = $this->recruitingManager->sessionMatch($order);
    $user = User::load($this->currentUser->id());
    foreach ($matches as $product_id => $match) {
      $recruiting = $this->recruitingManager->createRecruiting($match['order_item'], $match['recruiting_config']->getRecruiter(), $user, $match['recruiting_config'], $match['bonus']);
      $recruiting->save();
    }
  }

}
