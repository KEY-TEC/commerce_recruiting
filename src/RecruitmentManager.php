<?php

namespace Drupal\commerce_recruiting;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_recruiting\Entity\CampaignInterface;
use Drupal\commerce_recruiting\Entity\Recruitment;
use Drupal\commerce_recruiting\Entity\CampaignOption;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Class RecruitmentManager.
 */
class RecruitmentManager implements RecruitmentManagerInterface {

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
   * The recruitment session.
   *
   * @var \Drupal\commerce_recruiting\RecruitmentSessionInterface
   */
  private $recruitmentSession;

  /**
   * The module handler to invoke the alter hook with.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * RecruitmentManager constructor.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_account
   *   The current account.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce_recruiting\RecruitmentSessionInterface $recruitment_session
   *   The recruitment session.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(AccountInterface $current_account, LanguageManagerInterface $language_manager, EntityTypeManagerInterface $entity_type_manager, RecruitmentSessionInterface $recruitment_session, ModuleHandlerInterface $module_handler) {
    $this->currentAccount = $current_account;
    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->recruitmentSession = $recruitment_session;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritDoc}
   */
  public function getTotalBonusPerUser($uid, $include_paid_out = FALSE) {
    $query = $this->entityTypeManager->getStorage('commerce_recruitment')
      ->getQuery()
      ->condition('recruiter', $uid)
      ->condition('state', 'paid');

    $recruitment_ids = $query->execute();
    $recruitments = Recruitment::loadMultiple($recruitment_ids);
    $total_price = NULL;
    foreach ($recruitments as $recruitment) {
      /* @var \Drupal\commerce_recruiting\Entity\RecruitmentInterface $recruitment */
      if ($bonus = $recruitment->getBonus()->toPrice()) {
        $total_price = $total_price ? $total_price->add($bonus) : $bonus;
      }
    }

    return $total_price;
  }

  /**
   * Found matches in session.
   *
   * @param \Drupal\Core\Session\AccountInterface $recruiter
   *   The recruiter.
   * @param \Drupal\commerce_recruiting\Entity\CampaignOption $option
   *   The campaign option.
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order.
   *
   * @return array
   *   The matches.
   */
  private function sessionMatchByConfig(AccountInterface $recruiter, CampaignOption $option, OrderInterface $order) {
    $product = $option->getProduct();
    $matches = [];
    if (empty($product)) {
      // Missing product.
      return $matches;
    }

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
    $option = $this->recruitmentSession->getCampaignOption();
    $recruiter = $this->recruitmentSession->getRecruiter();
    if (empty($option) || empty($recruiter)) {
      // No or invalid recruiting session.
      return;
    }

    // First check the matches for stored config.
    $matches = $this->sessionMatchByConfig($recruiter, $option, $order);

    // Now check additional configs from this recruiter.
    $addition_campaigns = $this->entityTypeManager->getStorage('commerce_recruitment_campaign')
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
    $recruitments = $this->entityTypeManager->getStorage('commerce_recruitment')
      ->loadByProperties([
        'state' => 'created',
        'status' => 1,
      ]);

    foreach ($recruitments as $recruitment) {
      /** @var \Drupal\commerce_recruiting\Entity\RecruitmentInterface $recruitment */
      $recruitment->getState()->applyTransitionById($state);
      if ($recruitment->getState()->isValid()) {
        $recruitment->save();
      }
    }
  }

  /**
   * {@inheritDoc}
   */
  public function createRecruitment(OrderItemInterface $order_item, AccountInterface $recruiter, AccountInterface $recruited, CampaignOption $option, Price $bonus) {
    return Recruitment::create([
      'recruiter' => ['target_id' => $recruiter->id()],
      'name' => ['value' => substr($recruited->getAccountName() . ' by: ' . $recruiter->getAccountName(),0,49)],
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
  public function getRecruitmentSummaryByCampaign(CampaignInterface $campaign, $state, AccountInterface $recruiter = NULL) {
    $recruitments = $this->findRecruitmentsByCampaign($campaign, $state, $recruiter);
    // Allow modules to alter the set of recruitments used in this summary.
    $this->moduleHandler->alter('recruitment_summary_recruitments', $recruitments, $campaign);

    $price = NULL;
    $results = [];
    foreach ($recruitments as $recruitment) {
      $product = $recruitment->getProduct() instanceof ProductVariation ? $recruitment->getProduct()
        ->getProduct() : $recruitment->getProduct();
      $unique_key = $product
        ->id() . ' ' . $product->getEntityTypeId();

      if ($price == NULL) {
        $price = new Price($recruitment->getBonus()
          ->getNumber(), $recruitment->getBonus()->getCurrencyCode());
      }
      else {
        $price = $price->add($recruitment->getBonus());
      }

      if (!isset($results[$unique_key])) {
        $results[$unique_key] = new RecruitmentResult($product->label(), $recruitment->getBonus());
      }
      else {
        $results[$unique_key]->addPrice($recruitment->getBonus());
        $results[$unique_key]->counterIncrement();
      }
    }

    if ($price == NULL) {
      $price = new Price(0, "EUR");
    }

    return new RecruitmentSummary($price, $campaign, count($recruitments), $results);
  }

  /**
   * {@inheritDoc}
   */
  public function findRecruitmentsByCampaign(CampaignInterface $campaign, $state, AccountInterface $recruiter = NULL) {
    $option_ids = [];
    foreach ($campaign->getOptions() as $option) {
      $option_ids[] = $option->id();
    }

    $query = $this->entityTypeManager->getStorage('commerce_recruitment')
      ->getQuery();
    $query->condition('state', $state);
    $query->condition('campaign_option.entity.id', $option_ids, 'in');

    if ($recruiter != NULL) {
      $query->condition('recruiter', $recruiter->id());
    }

    $ids = $query->execute();
    if (count($ids) !== 0) {
      return $this->entityTypeManager->getStorage('commerce_recruitment')
        ->loadMultiple($ids);
    }
    else {
      return [];
    }
  }

}
