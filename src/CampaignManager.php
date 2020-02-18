<?php

namespace Drupal\commerce_recruiting;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Class CampaignManager.
 */
class CampaignManager implements CampaignManagerInterface {

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
  public function saveRecruitingSession(Code $code) {
    $option = $this->findCampaignOptionFromCode($code);
    $campaign = $option->getCampaign();
    if ($campaign->getRecruiter() == NULL && $code->getRecruiterId() == NULL) {
      throw new \InvalidArgumentException("No valid code");
    }
    if ($campaign->getRecruiter() == NULL && $code->getRecruiterId() != NULL) {
      $recruiter = $this->entityTypeManager->getStorage('user')->load($code->getRecruiterId());
    }
    else {
      $recruiter = $campaign->getRecruiter();
    }
    $this->recruitingSession->setRecruiter($recruiter);
    $this->recruitingSession->setRecruitingCampaignOption($option);
    return $this->recruitingSession;
  }

  /**
   * {@inheritDoc}
   */
  public function findNoRecruiterCampaigns(EntityInterface $product = NULL) {
    $query = $this->entityTypeManager->getStorage('commerce_recruiting_campaign')
      ->getQuery();

    $query->condition('status', 1);
    $options_query = $this->entityTypeManager->getStorage('commerce_recruiting_camp_option')
      ->getQuery();

    if ($product !== NULL) {
      $options_query
        ->condition('product.target_id', $product->id())
        ->condition('product.target_type', $product->getEntityTypeId());
    }
    else {
      $options_query
        ->notExists('product.target_id');
    }
    $options = $options_query->execute();
    if (!empty($options)) {
      $query
        ->condition('options', $options, 'IN');
    }

    $rcids = $query->execute();
    return $this->entityTypeManager->getStorage('commerce_recruiting_campaign')
      ->loadMultiple($rcids);
  }

  /**
   * {@inheritDoc}
   */
  public function findRecruiterCampaigns(AccountInterface $recruiter = NULL) {
    $query = $this->entityTypeManager->getStorage('commerce_recruiting_campaign')
      ->getQuery();
    $query->condition('status', 1);

    if ($recruiter !== NULL) {
      $query
        ->condition('recruiter', $recruiter->id(), '=');
    }
    else {
      $query
        ->notExists('recruiter');
    }

    $rcids = $query->execute();
    return $this->entityTypeManager->getStorage('commerce_recruiting_campaign')
      ->loadMultiple($rcids);
  }

  /**
   * {@inheritDoc}
   */
  public function findCampaignOptionFromCode(Code $code) {
    $query = $this->entityTypeManager->getStorage('commerce_recruiting_camp_option')
      ->getQuery();

    $query->condition('status', 1);
    $query->condition('code', $code->getCode());

    $rcids = $query->execute();
    $cid = current($rcids);
    if (!empty($cid)) {
      return $this->entityTypeManager->getStorage('commerce_recruiting_camp_option')
        ->load($cid);
    }
  }

}
