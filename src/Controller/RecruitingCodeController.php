<?php

namespace Drupal\commerce_recruitment\Controller;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Controller\ControllerBase;
use Drupal\commerce_recruitment\Encryption;
use Drupal\commerce_recruitment\RecruitingServiceInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class RecruitingCodeController.
 */
class RecruitingCodeController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\commerce_recruitment\RecruitingServiceInterface $recruiting_service
   *   The recruiting service.
   * @param \Symfony\Component\HttpFoundation\Session\Session $session
   *   The current session.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountProxyInterface $current_user, RecruitingServiceInterface $recruiting_service, Session $session) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
    $this->recruitingService = $recruiting_service;
    $this->session = $session;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
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
    // Default redirect on error.
    $redirect = $this->redirect('<front>');

    $decrypted = Encryption::decrypt($recruiting_code);
    $values = explode(';', $decrypted);

    // 3 values expected.
    if (count($values) !== 3) {
      return $redirect;
    }

    $uid = $values['0'];
    $pid = $values['1'];
    $entity_type = $values['2'];

    try {
      $entity_definition = $this->entityTypeManager->getStorage($entity_type);
    }
    catch (PluginException $e) {
      // Invalid entity type.
      return $redirect;
    }

    $route_name = 'entity.' . $entity_type . '.canonical';

    $redirect = $this->redirect($route_name, [$entity_type => $pid]);
    if ($this->currentUser->id() == $uid) {
      \Drupal::messenger()->addMessage(t('You can not use your own recommendation url.'));
      return $redirect;
    }



    if (true) {
      $data = [
        "uid" => $uid,
        "pid" => $pid,
        "type" => $entity_type
      ];
      $this->session->set("recruiting_data", $data);
    }
  }

}
