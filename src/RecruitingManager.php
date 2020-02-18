<?php

namespace Drupal\commerce_recruiting;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_recruiting\Entity\CampaignInterface;
use Drupal\commerce_recruiting\Entity\Recruiting;
use Drupal\commerce_recruiting\Entity\CampaignOption;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;

/**
 * Class RecruitingManager.
 */
class RecruitingManager implements RecruitingManagerInterface {

  /**
   * The current account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentAccount;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The recruiting session.
   *
   * @var \Drupal\commerce_recruiting\RecruitingSessionInterface
   */
  private $recruitingSession;

  /**
   * RecruitingManager constructor.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_account
   *   The current account.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce_recruiting\RecruitingSessionInterface $recruiting_session
   *   The recruiting session.
   */
  public function __construct(AccountInterface $current_account, LanguageManagerInterface $language_manager, EntityTypeManagerInterface $entity_type_manager, RecruitingSessionInterface $recruiting_session) {
    $this->currentAccount = $current_account;
    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->recruitingSession = $recruiting_session;
  }

  /**
   * {@inheritDoc}
   */
  public function getTotalBonusPerUser($uid, $include_paid_out = FALSE, $recruiting_type = NULL) {
    $query = $this->entityTypeManager->getStorage('commerce_recruiting')
      ->getQuery()
      ->condition('recruiter', $uid)
      ->condition('state', 'paid');

    if ($recruiting_type !== NULL) {
      $query->condition('type', $recruiting_type);
    }

    $recruiting_ids = $query->execute();
    $recruitings = Recruiting::loadMultiple($recruiting_ids);
    $total_price = NULL;
    foreach ($recruitings as $recruit) {
      /* @var \Drupal\commerce_recruiting\Entity\RecruitingInterface $recruit */
      if ($bonus = $recruit->getBonus()->toPrice()) {
        $total_price = $total_price ? $total_price->add($bonus) : $bonus;
      }
    }
    return $total_price;
  }

  /**
   * Found matches in session.
   *
   * @param \Drupal\user\Entity\User $recruiter
   *   The recruiter.
   * @param \Drupal\commerce_recruiting\Entity\CampaignOption $option
   *   The recruiting campaign option.
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order.
   *
   * @return array
   *   The matches.
   */
  private function sessionMatchByConfig(User $recruiter, CampaignOption $option, OrderInterface $order) {
    $product = $option->getProduct();
    $matches = [];
    foreach ($order->getItems() as $item) {
      $purchased_product = $item->getPurchasedEntity();
      if ($purchased_product instanceof ProductVariation) {
        $purchased_product = $purchased_product->getProduct();
      }
      if ($purchased_product->id() === $product->id()) {
        $matches[$purchased_product->id()] = [
          'campaign_option' => $option,
          'order_item' => $item,
          'bonus' => $option->calculateBonus($item),
          'recruiter' => $recruiter,
        ];
      }
    }
    return $matches;
  }

  /**
   * {@inheritDoc}
   */
  public function sessionMatch(OrderInterface $order) {
    $option = $this->recruitingSession->getCampaignOption();
    $recruiter = $this->recruitingSession->getRecruiter();

    // First check the matches for stored config.
    $matches = $this->sessionMatchByConfig($recruiter, $option, $order);

    // Now check additional configs from this recruiter.
    $addition_campaigns = $this->entityTypeManager->getStorage('commerce_recruiting_campaign')
      ->loadByProperties([
        'recruiter' => $recruiter->id(),
        'status' => 1,
      ]);
    /** @var \Drupal\commerce_recruiting\Entity\CampaignInterface $campaign */
    foreach ($addition_campaigns as $campaign) {
      foreach ($campaign->getOptions() as $option) {
        if ($option->isPublished()) {
          $additional_matches = $this->sessionMatchByConfig($recruiter, $option, $order);
          foreach ($additional_matches as $product_id => $additional_match) {
            if (isset($matches[$product_id])) {
              // Only use the one with higher bonus per product.
              if ($matches[$product_id]['bonus']->getNumber() >= $additional_matches[$product_id]['bonus']->getNumber()) {
                $matches[$product_id] = $additional_matches[$product_id];
              }
            }
          }
        }
      }
    }
    return $matches;
  }

  /**
   * {@inheritDoc}
   */
  public function applyTransitions($state) {
    $recruitings = $this->entityTypeManager->getStorage('commerce_recruiting')
      ->loadByProperties([
        'state' => 'created',
        'status' => 1,
      ]);
    /** @var \Drupal\commerce_recruiting\Entity\RecruitingInterface $recruiting */
    foreach ($recruitings as $recruiting) {
      $recruiting->getState()->applyTransitionById($state);
      if ($recruiting->getState()->isValid()) {
        $recruiting->save();
      }
    }
  }

  /**
   * {@inheritDoc}
   */
  public function createRecruiting(OrderItemInterface $order_item, User $recruiter, User $recruited, CampaignOption $option, Price $bonus) {
    return Recruiting::create([
      'recruiter' => ['target_id' => $recruiter->id()],
      'name' => ['value' => $recruited->getAccountName() . ' by: ' . $recruiter->getAccountName()],
      'campaign_option' => ['target_id' => $option->id()],
      'recruited' => ['target_id' => $recruited->id()],
      'order_item' => ['target_id' => $order_item->id()],
      'status' => 1,
      'product' => [
        'target_id' => $order_item->getPurchasedEntityId(),
        'target_type' => $order_item->getPurchasedEntity()->getEntityTypeId(),
      ],
      'bonus' => $bonus,
    ]);
  }

  /**
   * {@inheritDoc}
   */
  public function recruitingSummaryByCampaign(CampaignInterface $campaign, $state) {
    $recruitings = $this->findRecruitingByCampaign($campaign, $state);
    $price = NULL;
    $results = [];
    foreach ($recruitings as $recruiting) {
      $product = $recruiting->getProduct() instanceof ProductVariation ? $recruiting->getProduct()
        ->getProduct() : $recruiting->getProduct();
      $unique_key = $product
        ->id() . ' ' . $product->getEntityTypeId();

      if ($price == NULL) {
        $price = new Price($recruiting->getBonus()
          ->getNumber(), $recruiting->getBonus()->getCurrencyCode());
      }
      else {
        $price = $price->add($recruiting->getBonus());
      }
      if (!isset($results[$unique_key])) {
        $results[$unique_key] = new RecruitingResult($product->label(), $recruiting->getBonus());
      }
      else {
        $results[$unique_key]->addPrice($recruiting);
      }

    }
    return new RecruitingSummary($price, $results);
  }

  /**
   * {@inheritDoc}
   */
  public function findRecruitingByCampaign(CampaignInterface $campaign, $state) {
    $option_ids = [];
    foreach ($campaign->getOptions() as $option) {
      $option_ids[] = $option->id();
    }
    $query = $this->entityTypeManager->getStorage('commerce_recruiting')
      ->getQuery();
    $query->condition('state', $state);
    $query->condition('campaign_option.entity.id', $option_ids, 'in');
    $ids = $query->execute();
    if (count($ids) !== 0) {
      return $this->entityTypeManager->getStorage('commerce_recruiting')
        ->loadMultiple($ids);
    }
    else {
      return [];
    }
  }

}
