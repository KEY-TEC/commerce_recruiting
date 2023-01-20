<?php

namespace Drupal\commerce_recruiting;

use Drupal\commerce_recruiting\Event\RecruitmentSessionEvent;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Manager service class for recruitment campaigns.
 */
class CampaignManager implements CampaignManagerInterface {

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
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * CampaignManager constructor.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce_recruiting\RecruitmentSessionInterface $recruitment_session
   *   The recruitment session.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   */
  public function __construct(LanguageManagerInterface $language_manager, EntityTypeManagerInterface $entity_type_manager, RecruitmentSessionInterface $recruitment_session, EventDispatcherInterface $event_dispatcher) {
    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->recruitmentSession = $recruitment_session;
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * {@inheritDoc}
   */
  public function getRecruiterFromCode(Code $code) {
    $option = $this->findCampaignOptionFromCode($code);
    if ($option === NULL) {
      throw new \Exception('No option found for ' . $code->getCode());
    }
    $campaign = $option->getCampaign();
    if ($code->getRecruiterCode() != NULL) {
      $user_storage = $this->entityTypeManager->getStorage('user');
      $uid = $code->getRecruiterCode();
      if (is_numeric($uid)) {
        $recruiter = $user_storage->load($uid);
      }
      else {
        $recruiter = current($user_storage->loadByProperties(['code' => $uid]));
      }
    }
    elseif ($campaign->getRecruiter() != NULL) {
      $recruiter = $campaign->getRecruiter();
    }

    return $recruiter;
  }

  /**
   * {@inheritDoc}
   */
  public function saveRecruitmentSession(Code $code) {
    $option = $this->findCampaignOptionFromCode($code);
    $recruiter = $this->getRecruiterFromCode($code);

    if (empty($recruiter)) {
      throw new \InvalidArgumentException("No valid code");
    }

    $this->recruitmentSession->setRecruiter($recruiter);
    $this->recruitmentSession->setCampaignOption($option);

    // Create and dispatch RecruitmentSession event.
    $event = new RecruitmentSessionEvent($this->recruitmentSession);
    $this->eventDispatcher->dispatch(RecruitmentSessionEvent::SESSION_SET_EVENT, $event);

    return $this->recruitmentSession;
  }

  /**
   * {@inheritDoc}
   */
  public function findNoRecruiterCampaigns(EntityInterface $product = NULL) {
    $query = $this->entityTypeManager->getStorage('commerce_recruitment_campaign')
      ->getQuery();

    $query->condition('status', 1);
    $options_query = $this->entityTypeManager->getStorage('commerce_recruitment_camp_option')
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
    return $this->entityTypeManager->getStorage('commerce_recruitment_campaign')
      ->loadMultiple($rcids);
  }

  /**
   * {@inheritDoc}
   */
  public function findRecruiterCampaigns(AccountInterface $recruiter = NULL) {
    $query = $this->entityTypeManager->getStorage('commerce_recruitment_campaign')
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
    return $this->entityTypeManager->getStorage('commerce_recruitment_campaign')
      ->loadMultiple($rcids);
  }

  /**
   * {@inheritDoc}
   */
  public function findCampaignOptionFromCode(Code $code) {
    $query = $this->entityTypeManager->getStorage('commerce_recruitment_camp_option')
      ->getQuery();

    $query->condition('status', 1);
    $query->condition('code', $code->getCode());

    $rcids = $query->execute();
    $cid = current($rcids);
    if (!empty($cid)) {
      return $this->entityTypeManager->getStorage('commerce_recruitment_camp_option')
        ->load($cid);
    }
  }

}
