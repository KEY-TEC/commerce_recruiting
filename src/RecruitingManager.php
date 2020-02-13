<?php

namespace Drupal\commerce_recruitment;

use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\commerce_recruitment\Entity\RecruitingConfig;
use Drupal\commerce_recruitment\Entity\RecruitingEntity;
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
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritDoc}
   */
  public function getPublicRecruitingLink(AccountInterface $account = NULL, ProductInterface $product = NULL) {
    $this->entityTypeManager->getStorage('commerce_recruiting_config')->loadByProperties([
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

  /**
   * {@inheritDoc}
   */
  public function getRecruitingUrl(RecruitingConfig $recruiting_config, User $recruiter = NULL) {
    $code = $this->getRecruitingCode($recruiting_config, $recruiter);
    return Url::fromRoute('commerce_recruitment.recruiting_url', ['recruiting_code' => $code], ['absolute' => TRUE]);
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
  public function getRecruitingCode(RecruitingConfig $recruiting_config, User $recruiter = NULL) {
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
