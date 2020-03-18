<?php

namespace Drupal\commerce_recruiting\Event;

use Drupal\commerce_recruiting\RecruitmentSessionInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * RecruitmentSession event.
 */
class RecruitmentSessionEvent extends Event {

  const SESSION_SET_EVENT = 'commerce_recruiting_recruitment_session_event';

  /**
   * The recruitment session.
   *
   * @var \Drupal\commerce_recruiting\RecruitmentSessionInterface
   */
  public $session;

  /**
   * Constructs the object.
   *
   * @param \Drupal\commerce_recruiting\RecruitmentSessionInterface $session
   *   The recruitment session.
   */
  public function __construct(RecruitmentSessionInterface $session) {
    $this->session = $session;
  }

}
