<?php

namespace Drupal\commerce_recruitment;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_recruitment\Entity\RecruitingConfig;
use Drupal\commerce_recruitment\Entity\Recruiting;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\commerce_recruitment\Exception\InvalidLinkException;
use Drupal\Core\Url;
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
   * @var \Drupal\commerce_recruitment\RecruitingSessionInterface
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
   * @param \Drupal\commerce_recruitment\RecruitingSessionInterface $recruiting_session
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
  public function findRecruitingConfig(AccountInterface $recruiter = NULL, EntityInterface $product = NULL) {
    $query = $this->entityTypeManager->getStorage('commerce_recruiting_config')
      ->getQuery();
    $query->condition('status', 1);
    if ($product !== NULL) {
      $query
      // ->condition('product__target_type', $product->getEntityTypeId())
        ->condition('products', $product->id());

    }
    if ($recruiter !== NULL) {
      $query
        ->condition('recruiter', $recruiter->id(), '=');
    }
    else {
      $query
        ->notExists('recruiter');
    }

    $rcids = $query->execute();
    return $this->entityTypeManager->getStorage('commerce_recruiting_config')
      ->loadMultiple($rcids);
  }

  /**
   * {@inheritDoc}
   */
  public function getTotalBonusPerUser($uid, $include_paid_out = FALSE, $recruitment_type = NULL) {
    $query = $this->entityTypeManager->getStorage('commerce_recruiting')
      ->getQuery()
      ->condition('recruiter', $uid)
      ->condition('state', 'paid');

    if ($recruitment_type !== NULL) {
      $query->condition('type', $recruitment_type);
    }

    $recruiting_ids = $query->execute();
    $recruitings = Recruiting::loadMultiple($recruiting_ids);
    $total_price = NULL;
    foreach ($recruitings as $recruit) {
      /* @var \Drupal\commerce_recruitment\Entity\RecruitingInterface $recruit */
      if ($bonus = $recruit->getBonus()->toPrice()) {
        $total_price = $total_price ? $total_price->add($bonus) : $bonus;
      }
    }
    return $total_price;
  }

  /**
   * {@inheritDoc}
   */
  public function getRecruitingUrl(RecruitingConfig $recruiting_config, AccountInterface $recruiter = NULL) {
    $code = $this->getRecruitingCode($recruiting_config, $recruiter);
    return Url::fromRoute('commerce_recruitment.recruiting_url', ['recruiting_code' => $code], ['absolute' => TRUE]);
  }

  /**
   * {@inheritDoc}
   */
  public function getRecruitingSessionFromCode($code) {
    $info = $this->getRecruitingInfoFromCode($code);
    $this->recruitingSession->setRecruiter($info['recruiter']);
    $this->recruitingSession->setRecruitingConfig($info['recruiting_config']);
    return $this->recruitingSession;
  }

  /**
   * Found matches in session.
   *
   * @param \Drupal\user\Entity\User $recruiter
   *   The recruiter.
   * @param \Drupal\commerce_recruitment\Entity\RecruitingConfig $config
   *   The recruiting config.
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order.
   *
   * @return array
   *   The matches.
   */
  private function sessionMatchByConfig(User $recruiter, RecruitingConfig $config, OrderInterface $order) {
    $products = $config->getProducts();
    $session_product_ids = [];
    $matches = [];
    foreach ($products as $product) {
      $session_product_ids[] = $product->id();
    }
    foreach ($order->getItems() as $item) {
      if (in_array($item->getPurchasedEntity()->id(), $session_product_ids)) {
        $matches[$item->getPurchasedEntity()->id()] = [
          'recruiting_config' => $config,
          'order_item' => $item,
          'bonus' => $config->calculateBonus($item),
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
    $config = $this->recruitingSession->getRecruitingConfig();
    $recruiter = $this->recruitingSession->getRecruiter();
    if ($config === NULL) {
      return [];
    }
    // First check the matches for stored config.
    $matches = $this->sessionMatchByConfig($recruiter, $config, $order);

    // Now check additional configs from this recruiter.
    $addition_configs = $this->entityTypeManager->getStorage('commerce_recruiting_config')
      ->loadByProperties([
        'recruiter' => $recruiter->id(),
        'status' => 1,
      ]);
    foreach ($addition_configs as $config) {
      $additional_matches = $this->sessionMatchByConfig($recruiter, $config, $order);
      foreach ($additional_matches as $product_id => $additional_match) {
        if (isset($matches[$product_id])) {
          // Only use the one with higher bonus per product.
          if ($matches[$product_id]['bonus']->getNumber() >= $additional_matches[$product_id]['bonus']->getNumber()) {
            $matches[$product_id] = $additional_matches[$product_id];
          }
        }
      }
    }
    return $matches;
  }

  /**
   * {@inheritDoc}
   */
  public function createRecruiting(OrderItemInterface $order_item, User $recruiter, User $recruited, RecruitingConfig $config, Price $bonus) {
    return Recruiting::create([
      'recruiter' => ['target_id' => $recruiter->id()],
      'name' => ['value' => $recruited->getAccountName() . ' by: ' . $recruiter->getAccountName()],
      'recruiting_config' => ['target_id' => $recruiter->id()],
      'recruited' => ['target_id' => $recruited->id()],
      'order_item' => ['target_id' => $order_item->id()],
      'status' => 1,
      'product' => [
        'target_id' => $order_item->getPurchasedEntity()->id(),
        'target_type' => $order_item->getPurchasedEntity()->getEntityTypeId(),
      ],
      'bonus' => $bonus,
    ]);
  }

  /**
   * {@inheritDoc}
   */
  public function getRecruitingInfoFromCode($code) {
    $decrypted = Encryption::decrypt($code);
    $values = explode(';', $decrypted);
    if (!is_array($values)) {
      throw new InvalidLinkException("Invalid link. Code $code seems to invalid. $decrypted");
    }
    $rid = isset($values[0]) ? $values[0] : NULL;
    $rcid = isset($values[1]) ? $values[1] : NULL;
    if ($rid === NULL || $rcid === NULL) {
      throw new InvalidLinkException("Invalid link. Code $code seems to incomplete.");
    }
    $recruiter = $this->entityTypeManager->getStorage('user')->load($rid);
    if ($recruiter == NULL) {
      throw new InvalidLinkException("Invalid link. Code $code seems to incomplete. No recruiter found.");
    }
    $recruiting_config = $this->entityTypeManager->getStorage('commerce_recruiting_config')
      ->load($rcid);
    if ($recruiting_config == NULL) {
      throw new InvalidLinkException("Invalid link. Code $code seems to incomplete. No recruiting config found");
    }
    return [
      'recruiter' => $recruiter,
      'recruiting_config' => $recruiting_config,
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function getRecruitingCode(RecruitingConfig $recruiting_config, AccountInterface $recruiter = NULL) {
    if ($recruiter == NULL) {
      $recruiter = $recruiting_config->getRecruiter();
    }
    if ($recruiter == NULL) {
      throw new \InvalidArgumentException(sprintf('Missing recruiter for config "%s".', $recruiting_config->id()));
    }
    if (empty($recruiting_config->id())) {
      throw new \InvalidArgumentException('Invalid recruiting_config id');
    }

    $values = [$recruiter->id(), $recruiting_config->id()];
    return Encryption::encrypt(implode(';', $values));
  }

}
