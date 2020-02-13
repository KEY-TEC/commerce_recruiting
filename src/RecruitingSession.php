<?php

namespace Drupal\commerce_recruitment;

use Drupal\commerce_recruitment\Entity\RecruitingConfig;
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
      self::RECRUITER => 'commerce_recruitment_rid',
      self::RECRUITING_CONFIG => 'commerce_recruitment_rcid',
    ];
    if (!isset($keys[$type])) {
      throw new \InvalidArgumentException(sprintf('Unknown type "%s".', $type));
    }

    return $keys[$type];
  }

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
   */
  public function __construct(SessionInterface $session) {
    $this->session = $session;
  }

  /**
   * {@inheritDoc}
   */
  public function getRecruiter() {

    // TODO: Implement getRecruiter() method.
  }

  /**
   * {@inheritDoc}
   */
  public function getRecruitingConfig() {
    // TODO: Implement getRecruitingConfig() method.
  }

  /**
   * {@inheritDoc}
   */
  public function setRecruiter(User $recruiter) {
    $this->session->set('c');
  }

  /**
   * {@inheritDoc}
   */
  public function setRecruitingConfig(RecruitingConfig $recruiting) {
    // TODO: Implement setRecruitingConfig() method.
  }

}
