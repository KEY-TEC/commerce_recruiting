<?php

namespace Drupal\commerce_recruitment;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Default implementation of the cart session.
 */
class RecrutingSession implements RecrutingSessionInterface {

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
  public function getRecruter() {
    // TODO: Implement getRecruter() method.
  }

  /**
   * {@inheritDoc}
   */
  public function getRecrutingConfig() {
    // TODO: Implement getRecrutingConfig() method.
  }

}
