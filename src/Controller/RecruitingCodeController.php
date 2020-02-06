<?php

namespace Drupal\commerce_recruitment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\commerce_recruitment\Encryption;
use Drupal\commerce_recruitment\RecruitingServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class RecruitingCodeController.
 */
class RecruitingCodeController extends ControllerBase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The recruiting service.
   *
   * @var \Drupal\commerce_recruitment\RecruitingServiceInterface
   */
  protected $recruitingService;

  /**
   * The current session.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  protected $session;

  /**
   * Constructs a new RecruitingCodeController object.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\commerce_recruitment\RecruitingServiceInterface $recruiting_service
   *   The recruiting service.
   * @param \Symfony\Component\HttpFoundation\Session\Session $session
   *   The current session.
   */
  public function __construct(AccountProxyInterface $current_user, RecruitingServiceInterface $recruiting_service, Session $session) {
    $this->currentUser = $current_user;
    $this->recruitingService = $recruiting_service;
    $this->session = $session;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('commerce_recruitment.recruiting'),
      $container->get('session')
    );
  }

  /**
   * Decrypt recruiting url and redirect to product.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to product.
   */
  public function redirectFromRecruitingUrl($recruiting_code) {
    $decrypted = Encryption::decrypt($recruiting_code);
    $values = explode(';', $decrypted);
    if (!empty($values)) {
      $uid = $values['0'];
      $pid = $values['1'];
      $type = $this->recruitingService->getCommerceEntityBundle($values['2']);
      $route_name = "entity." . $type . ".canonical";

      $redirect = $this->redirect($route_name, [$type => $pid]);
      if ($this->currentUser->id() == $uid) {
        \Drupal::messenger()->addMessage(t('You can not use your own recommendation url.'));
        return $redirect;
      }
      else {
        $data = [
          "uid" => $uid,
          "pid" => $pid,
          "type" => $type
        ];
        $this->session->set("recruiting_data", $data);
      }
      return $redirect;
    }
  }

}
