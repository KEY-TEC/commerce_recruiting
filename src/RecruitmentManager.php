<?php

namespace Drupal\commerce_recruiting;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_recruiting\Entity\CampaignInterface;
use Drupal\commerce_recruiting\Entity\CampaignOptionInterface;
use Drupal\commerce_recruiting\Entity\Recruitment;
use Drupal\commerce_recruiting\Entity\CampaignOption;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RecruitmentManager.
 */
class RecruitmentManager implements RecruitmentManagerInterface {

  /**
   * The service container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  private $container;

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
   * The module handler to invoke the alter hook with.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * RecruitmentManager constructor.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   * @param \Drupal\Core\Session\AccountInterface $current_account
   *   The current account.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger factory.
   */
  public function __construct(ContainerInterface $container, AccountInterface $current_account, LanguageManagerInterface $language_manager, EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler, LoggerChannelFactoryInterface $logger) {
    $this->container = $container;
    $this->currentAccount = $current_account;
    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $module_handler;
    $this->logger = $logger->get('commerce_recruiting');
  }

  /**
   * {@inheritDoc}
   */
  public function createRecruitment(OrderItemInterface $order_item, AccountInterface $recruiter, AccountInterface $recruited, CampaignOption $option, Price $bonus) {
    return Recruitment::create([
      'recruiter' => ['target_id' => $recruiter->id()],
      'name' => [
        'value' => substr($recruited->getAccountName() . ' by: ' . $recruiter->getAccountName(), 0, 49),
      ],
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
  public function sessionMatch(OrderInterface $order) {
    $matches = [];

    $session = $this->getRecruitmentSession();
    $option = $session->getCampaignOption();
    $recruiter = $session->getRecruiter();
    if (empty($option) || empty($recruiter)) {
      // No or invalid recruiting session.
      return $matches;
    }

    $campaign = $option->getCampaign();
    if ($campaign->hasField('bonus_any_option') && $campaign->bonus_any_option->value) {
      // Order items may match any options of current campaign.
      $options = $campaign->getOptions();
    }
    else {
      // Only option from session can match order items.
      $options[] = $option;
    }

    // Check for matches in given options.
    foreach ($options as $option) {
      $product = $option->getProduct();
      if (empty($product)) {
        // Missing product.
        continue;
      }

      foreach ($order->getItems() as $order_item) {
        $purchased_product = $order_item->getPurchasedEntity();
        if ($purchased_product instanceof ProductVariation) {
          $purchased_product = $purchased_product->getProduct();
        }

        if ($purchased_product->id() === $product->id()) {
          $bonus = $this->resolveRecruitmentBonus($option, $order_item);
          if ($bonus instanceof Price) {
            // @todo: evaluate to turn this into an object.
            $matches[$purchased_product->id()] = [
              'campaign_option' => $option,
              'order_item' => $order_item,
              'bonus' => $bonus,
              'recruiter' => $recruiter,
            ];
          }
        }
      }
    }

    return $matches;
  }

  /**
   * {@inheritDoc}
   */
  public function serializeMatch(array $match) {
    foreach ($match as $key => $value) {
      if (is_object($value) && method_exists($value, 'id')) {
        // Does have an id to store.
        $match[$key] = $value->id();
      }
    }

    return $match;
  }

  /**
   * {@inheritDoc}
   */
  public function deserializeMatch(array $serialized_match) {
    foreach ($serialized_match as $name => $data) {
      $entity_type = NULL;
      switch ($name) {
        case 'campaign_option':
          $entity_type = 'commerce_recruitment_camp_option';
          break;

        case 'order_item':
          $entity_type = 'commerce_order_item';
          break;

        case 'recruiter':
          $entity_type = 'user';
          break;
      }

      if ($entity_type && $entity = $this->entityTypeManager->getStorage($entity_type)->load($data)) {
        $serialized_match[$name] = $entity;
      }
    }

    return $serialized_match;
  }

  /**
   * {@inheritDoc}
   */
  public function resolveRecruitmentBonus(CampaignOptionInterface $option, OrderItemInterface $order_item) {
    $campaign = $option->getCampaign();
    if ($campaign->hasField('recruitment_bonus_resolver') && $campaign->getBonusResolver()) {
      return $campaign->getBonusResolver()->resolveBonus($option, $order_item);
    }
    else {
      // Legacy.
      return $option->calculateBonus($order_item);
    }
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
      try {
        /** @var \Drupal\commerce_recruiting\Entity\RecruitmentInterface $recruitment */
        $recruitment->getState()->applyTransitionById($state);
        if ($recruitment->getState()->isValid()) {
          $recruitment->save();
        }
      }
      catch (\Exception $e) {
        // Transition is not allowed. Skip recruitment.
        $this->logger->debug('Recruitment ' . $recruitment->id() . ' transition skipped: ' . $e->getMessage());
      }
    }
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
   * Returns the recruitment session.
   *
   * @return \Drupal\commerce_recruiting\RecruitmentSessionInterface
   *   The recruitment session.
   */
  private function getRecruitmentSession() {
    return $this->container->get('commerce_recruiting.recruitment_session');
  }

}
