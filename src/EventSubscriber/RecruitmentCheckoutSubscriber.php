<?php

namespace Drupal\commerce_recruiting\EventSubscriber;

use Drupal\commerce_cart\CartProviderInterface;
use Drupal\commerce_cart\Event\CartEvents;
use Drupal\commerce_cart\Event\CartOrderItemAddEvent;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\commerce_recruiting\Entity\CampaignOptionInterface;
use Drupal\commerce_recruiting\Event\RecruitmentSessionEvent;
use Drupal\commerce_recruiting\RecruitmentManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber for dealing with recruitments.
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
  protected $recruitmentManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The cart provider.
   *
   * @var \Drupal\commerce_cart\CartProviderInterface
   */
  protected $cartProvider;

  /**
   * Constructs a new RecruitmentCheckoutSubscriber object.
   *
   * @param \Drupal\Core\Session\AccountProxy $current_user
   *   The current user.
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   The messenger.
   * @param \Drupal\commerce_recruiting\RecruitmentManagerInterface $recruitment_manager
   *   The recruitment manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce_cart\CartProviderInterface $cart_provider
   *   The cart provider.
   */
  public function __construct(AccountProxy $current_user, Messenger $messenger, RecruitmentManagerInterface $recruitment_manager, EntityTypeManagerInterface $entity_type_manager, CartProviderInterface $cart_provider) {
    $this->currentUser = $current_user;
    $this->messenger = $messenger;
    $this->recruitmentManager = $recruitment_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->cartProvider = $cart_provider;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      CartEvents::CART_ORDER_ITEM_ADD => 'onOrderItemAdd',
      RecruitmentSessionEvent::SESSION_SET_EVENT => 'onRecruitmentSessionSet',
      'commerce_order.place.post_transition' => 'onOrderPlace',
    ];
  }

  /**
   * Event handler on order item add to cart.
   *
   * Will save recruitment information from current session to order items.
   *
   * @param \Drupal\commerce_cart\Event\CartOrderItemAddEvent $event
   *   The event.
   */
  public function onOrderItemAdd(CartOrderItemAddEvent $event) {
    // Save match information in order item for later reference.
    $this->orderItemsSaveRecruitmentInfo($event->getCart(), [$event->getOrderItem()]);
  }

  /**
   * Event handler on recruitment session set.
   *
   * Will check all current user's carts to update order item recruitment infos
   * on matching products.
   *
   * @param \Drupal\commerce_recruiting\Event\RecruitmentSessionEvent $event
   *   The event.
   */
  public function onRecruitmentSessionSet(RecruitmentSessionEvent $event) {
    $carts = $this->cartProvider->getCarts();
    foreach ($carts as $cart) {
      $this->orderItemsSaveRecruitmentInfo($cart, $cart->getItems());
    }
  }

  /**
   * Save recruitment infos from current session in order items.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $cart
   *   The cart to check against recruitment session.
   * @param \Drupal\commerce_order\Entity\OrderItemInterface[] $update_order_items
   *   The order items to check against session matches.
   */
  protected function orderItemsSaveRecruitmentInfo(OrderInterface $cart, array $update_order_items) {
    $matches = $this->recruitmentManager->sessionMatch($cart);
    foreach ($matches as $match) {
      foreach ($update_order_items as $order_item) {
        if ($match['order_item']->id() == $order_item->id()) {
          $order_item->set('recruitment_info', [
            'campaign_option' => $match['campaign_option'],
            'recruiter' => $match['recruiter'],
            'number' => $match['bonus']->getNumber(),
            'currency_code' => $match['bonus']->getCurrencyCode(),
          ]);
          $order_item->save();
        }
      }
    }
  }

  /**
   * Event handler on order checkout.
   *
   * Creates a recruitment entity and references it in the order
   * if recruitment info is set in order item(s).
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The transition event.
   */
  public function onOrderPlace(WorkflowTransitionEvent $event) {
    /** @var \Drupal\commerce_order\Entity\Order $order */
    $order = $event->getEntity();
    $created_recruiments = FALSE;
    foreach ($order->getItems() as $order_item) {
      /** @var \Drupal\commerce_recruiting\Plugin\Field\FieldType\RecruitmentInfo $recruitment_info */
      if ($order_item->hasField('recruitment_info') && $recruitment_info = $order_item->recruitment_info->first()) {
        if (!empty($recruitment_info->getCampaignOption()) && !empty($recruitment_info->getRecruiter())) {
          // Re-evaluate bonus, in case campaign option bonus has changed.
          $bonus = $this->recruitmentManager->resolveRecruitmentBonus($recruitment_info->getCampaignOption(), $order_item);
          if ($bonus instanceof Price) {
            $this->createRecruitment(
              $order_item,
              $recruitment_info->getRecruiter(),
              $this->currentUser,
              $recruitment_info->getCampaignOption(),
              $bonus,
            );
            $created_recruiments = TRUE;
          }
        }
      }
    }
    if ($created_recruiments) {
      return;
    }

    // No recruitment was created. Check if user was recruited before.
    $recruitment_storage = $this->entityTypeManager->getStorage('commerce_recruitment');
    $user_recruitments = $recruitment_storage->loadByProperties(['recruited' => $order->getCustomerId()]);
    $user_campaigns = [];
    /** @var \Drupal\commerce_recruiting\Entity\RecruitmentInterface $user_recruitment */
    foreach ($user_recruitments as $user_recruitment) {
      /** @var \Drupal\commerce_recruiting\Entity\CampaignInterface $campaign */
      $campaign = $user_recruitment->campaign_option->entity->getCampaign();
      if ($campaign->hasField('auto_re_recruit') && $campaign->auto_re_recruit->value) {
        // Auto re recruit option is on, check for matching products in cart.
        if (in_array($campaign->id(), $user_campaigns, TRUE)) {
          // Only once per campaign.
          break;
        }

        $campaign_options = $campaign->getOptions();
        foreach ($campaign_options as $campaign_option) {
          foreach ($order->getItems() as $order_item) {
            $purchased_entity = $order_item->getPurchasedEntity();
            if ($purchased_entity instanceof ProductVariationInterface) {
              $purchased_entity = $purchased_entity->getProduct();
            }
            $campaign_product = $campaign_option->getProduct();
            if ($purchased_entity === $campaign_product) {
              // Match found, create new recruitment from existing.
              $bonus = $this->recruitmentManager->resolveRecruitmentBonus($campaign_option, $order_item);
              if ($bonus instanceof Price) {
                $this->createRecruitment($order_item, $user_recruitment->getOwner(), $order->getCustomer(), $campaign_option, $bonus);
                $user_campaigns[] = $campaign->id();
                break;
              }
            }
          }
        }
      }
    }
  }

  /**
   * Helper function to create recruitments from session match.
   *
   * @param \Drupal\commerce_order\Entity\OrderItemInterface $order_item
   *   The order item.
   * @param \Drupal\Core\Session\AccountInterface $recruiter
   *   The recruiter account.
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The recruited user account.
   * @param \Drupal\commerce_recruiting\Entity\CampaignOptionInterface $campaign_option
   *   The campaign option.
   * @param \Drupal\commerce_price\Price $bonus
   *   The bonus.
   * @param bool $save
   *   Set true to save the recruitment after creating.
   *
   * @return \Drupal\commerce_recruiting\Entity\RecruitmentInterface
   *   The created recruitment.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createRecruitment(OrderItemInterface $order_item, AccountInterface $recruiter, AccountInterface $user, CampaignOptionInterface $campaign_option, Price $bonus, $save = TRUE) {
    $recruitment = $this->recruitmentManager->createRecruitment($order_item, $recruiter, $user, $campaign_option, $bonus);
    if ($save) {
      $recruitment->save();
    }
    return $recruitment;
  }

}
