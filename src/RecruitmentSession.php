<?php

namespace Drupal\commerce_recruiting;

use Drupal\commerce_recruiting\Entity\CampaignOptionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Default implementation of the recruitment session.
 */
class RecruitmentSession implements RecruitmentSessionInterface {

  /**
   * Gets the session key for the given cart session type.
   *
   * @param string $type
   *   The cart session type.
   *
   * @return string
   *   The session key.
   *
   * @throws \InvalidArgumentException
   *   Thrown when the given $type is unknown.
   */
  protected function getSessionKey($type) {
    $keys = [
      self::RECRUITER => 'commerce_recruitment_rid',
      self::CAMPAIGN_OPTION => 'commerce_recruitment_coid',
    ];
    if (!isset($keys[$type])) {
      throw new \InvalidArgumentException(sprintf('Unknown type "%s".', $type));
    }

    return $keys[$type];
  }

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The session.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  protected $session;

  /**
   * Constructs a new CartSession object.
   *
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   The session.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(SessionInterface $session, EntityTypeManagerInterface $entity_type_manager) {
    $this->session = $session;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritDoc}
   */
  public function getRecruiter() {
    $rid = $this->session->get($this->getSessionKey(self::RECRUITER));
    if ($rid !== NULL) {
      return $this->entityTypeManager->getStorage('user')->load($rid);
    }
    return NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function getCampaignOption() {
    $rcid = $this->session->get($this->getSessionKey(self::CAMPAIGN_OPTION));
    if ($rcid !== NULL) {
      return $this->entityTypeManager->getStorage('commerce_recruitment_camp_option')->load($rcid);
    }
    return NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function setRecruiter(AccountInterface $recruiter) {
    $this->session->set($this->getSessionKey(self::RECRUITER), $recruiter->id());
  }

  /**
   * {@inheritDoc}
   */
  public function setCampaignOption(CampaignOptionInterface $campaign_option) {
    $this->session->set($this->getSessionKey(self::CAMPAIGN_OPTION), $campaign_option->id());
  }

}
