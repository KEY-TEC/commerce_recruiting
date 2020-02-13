<?php

namespace Drupal\commerce_recruitment;

use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\commerce_recruitment\Entity\RecruitingEntity;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;

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
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The recruiting config entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $configStorage;

  /**
   * RecruitingManager constructor.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_account
   *   The current account.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(AccountInterface $current_account, LanguageManagerInterface $language_manager, EntityTypeManagerInterface $entity_type_manager) {
    $this->currentAccount = $current_account;
    $this->languageManager = $language_manager;
    $this->configStorage = $entity_type_manager->getStorage('commerce_recruiting_config');
  }

  /**
   * {@inheritDoc}
   */
  public function getPublicRecruitingLink(AccountInterface $account = NULL, ProductInterface $product = NULL) {
    $this->configStorage->loadByProperties([
      'recruiter'
    ]);
  }

  /**
   * {@inheritDoc}
   */
  public function getTotalBonusPerUser($uid, $include_paid_out = FALSE, $recruitment_type = NULL) {
    $query = \Drupal::entityQuery('commerce_recruiting')
      ->condition('recruiter', $uid)
      ->condition('is_paid_out', (string) $include_paid_out);

    if ($recruitment_type !== NULL) {
      $query->condition('type', $recruitment_type);
    }

    $recruiting_ids = $query->execute();
    $recruitings = RecruitingEntity::loadMultiple($recruiting_ids);
    $total_price = NULL;
    foreach ($recruitings as $recruit) {
      /* @var \Drupal\commerce_recruitment\Entity\RecruitingEntityInterface $recruit */
      if ($bonus = $recruit->getBonus()->toPrice()) {
        $total_price = $total_price ? $total_price->add($bonus) : $bonus;
      }
    }
    return $total_price;
  }

}
