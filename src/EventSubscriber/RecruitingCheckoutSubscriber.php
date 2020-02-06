<?php

namespace Drupal\commerce_recruitment\EventSubscriber;

use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Session\AccountProxy;
use Drupal\commerce_recruitment\Entity\RecruitingEntity;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Session\Session;

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
   * The current session.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  protected $session;

  /**
   * Constructs a new RecruitingCheckoutSubscriber object.
   *
   * @param \Drupal\Core\Session\AccountProxy $current_user
   *   The current user.
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   The messenger.
   * @param \Symfony\Component\HttpFoundation\Session\Session $session
   *   The current session.
   */
  public function __construct(AccountProxy $current_user, Messenger $messenger, Session $session) {
    $this->currentUser = $current_user;
    $this->messenger = $messenger;
    $this->session = $session;
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
    /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
    $session = $this->session;
    $session_data = $session->get("recruiting_data");
    // User ID of the recruiter.
    $recruiter = $session_data['uid'];

    if (isset($recruiter)) {
      // Product ID of the recommended product.
      $product = $session_data['pid'];
      // Entity type of recommended entity (product or product bundle).
      $type = $session_data['type'];
      /** @var \Drupal\commerce_order\Entity\Order $order */
      $order = $event->getEntity();
      $total_friend_discount = '';
      if ($order->hasItems()) {
        $order_items = $order->getItems();
        foreach($order_items as $key => $order_item) {
          /** @var \Drupal\commerce_order\Entity\OrderItem $order_item */
          if ($order_item->hasPurchasedEntity()) {
            $purchasable_entity = $order_item->getPurchasedEntity();
            if ($purchasable_entity->hasField('field_friend_discount')) {
              /** @var \Drupal\commerce_price\Plugin\Field\FieldType\PriceItem $price_item */
              foreach ($purchasable_entity->field_friend_discount as $price_item) {
                /** @var \Drupal\commerce_price\Price $friend_discount */
                $friend_discount = $price_item->toPrice();
              }
              if (empty($total_friend_discount)) {
                $total_friend_discount = $friend_discount;
              }
              else {
                $total_friend_discount->add($friend_discount);
              }
            }
          }
        }
      }

      if ($this->isRecruited($recruiter) === FALSE) {
        $recruit = RecruitingEntity::create([
          "type" => "bonus",
          "name" => $recruiter . ' - ' . $product,
          "user_id" => $recruiter,
          "field_user_recruited" => $this->currentUser->id(),
          "field_" . $type => $product,
          "field_bonus" => $total_friend_discount,
          "field_paid_out" => "0",
        ]);

        try {
          $recruit->save();
        }
        catch (\Exception $e) {
          $this->messenger()->addError($e->getMessage());
        }

        if ($order->hasField('field_recruited')) {
          $order->field_recruited = $recruit;
          try {
            $order->save();
          }
          catch (\Exception $e) {
            $this->messenger()->addError($e->getMessage());
          }
        }
      }
    }
  }

  /**
   * Check if current user was already invited by the recruiter.
   *
   * @param int $uid_recruiter
   *   User ID from the inviter.
   *
   * @return bool
   *   Returns true on match found.
   */
  private function isRecruited($uid_recruiter) {
    $query = \Drupal::entityQuery('recruiting')
      ->condition('type', 'bonus')
      ->condition('user_id', $uid_recruiter)
      ->condition('field_user_recruited', $this->currentUser->id());
    $recruited = $query->execute();
    if (!empty($recruited)) {
      return TRUE;
    }
    return FALSE;
  }

}
