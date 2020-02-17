<?php

namespace Drupal\commerce_recruiting;

use Drupal\commerce_recruiting\Entity\CampaignOptionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Default implementation of the cart session.
 */
class RecruitingSession implements RecruitingSessionInterface {

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
      self::RECRUITER => 'commerce_recruiting_rid',
      self::CAMPAIGN_OPTION => 'commerce_recruiting_rcid',
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
      return $this->entityTypeManager->getStorage('commerce_recruiting_campaign')->load($rcid);
    }
    return NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function setRecruiter(User $recruiter) {
    $this->session->set($this->getSessionKey(self::RECRUITER), $recruiter->id());
  }

  /**
   * {@inheritDoc}
   */
  public function setRecruitingCampaignOption(CampaignOptionInterface $recruiting) {
    $this->session->set($this->getSessionKey(self::CAMPAIGN_OPTION), $recruiting->id());
  }

}
