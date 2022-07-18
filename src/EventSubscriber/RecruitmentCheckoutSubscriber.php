<?php

namespace Drupal\commerce_recruiting\EventSubscriber;

use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\commerce_recruiting\RecruitmentManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
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
  protected $recruitmentManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   */
  public function __construct(AccountProxy $current_user, Messenger $messenger, RecruitmentManagerInterface $recruitment_manager, EntityTypeManagerInterface $entity_type_manager) {
    $this->currentUser = $current_user;
    $this->messenger = $messenger;
    $this->recruitmentManager = $recruitment_manager;
    $this->entityTypeManager = $entity_type_manager;
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
    if (!empty($matches)) {
      foreach ($matches as $match) {
        $this->createRecruitmentFromMatch($match, $this->currentUser);
      }
      return;
    }

    // No session set. Check if user was recruited before.
    $recruitment_storage = $this->entityTypeManager->getStorage('commerce_recruitment');
    $user_recruitments = $recruitment_storage->loadByProperties(['recruited' => $order->getCustomerId()]);
    $user_campaigns = [];
    /** @var \Drupal\commerce_recruiting\Entity\RecruitmentInterface $user_recruitment */
    foreach ($user_recruitments as $user_recruitment) {
      /** @var \Drupal\commerce_recruiting\Entity\CampaignInterface $campaign */
      $campaign = $user_recruitment->campaign_option->entity->getCampaign();
      if (!in_array($campaign->id(), $user_campaigns, TRUE) && $campaign->hasField('auto_re_recruit') && $campaign->auto_re_recruit->value) {
        // Auto re recruit option is on, check for matching products.
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
                $recruitment = $this->recruitmentManager->createRecruitment($order_item, $user_recruitment->getOwner(), $order->getCustomer(), $campaign_option, $bonus);
                $recruitment->save();
                $user_campaigns[] = $campaign->id();
                break;
              }
            }
          }
          if (in_array($campaign->id(), $user_campaigns, TRUE)) {
            // Once per campaign.
            break;
          }
        }
      }
    }
  }

  /**
   * Helper function to create recruitments from session match.
   *
   * @param array $match
   *   The session match array.
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The recruited user account.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createRecruitmentFromMatch(array $match, AccountInterface $user) {
    $recruitment = $this->recruitmentManager->createRecruitment($match['order_item'], $match['recruiter'], $user, $match['campaign_option'], $match['bonus']);
    $recruitment->save();
  }

}
