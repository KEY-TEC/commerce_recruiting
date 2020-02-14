<?php

namespace Drupal\commerce_recruitment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\commerce_recruitment\RecruitingManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RecruitingCodeController.
 */
class RecruitingCodeController extends ControllerBase {

  /**
   * The recruiting service.
   *
   * @var \Drupal\commerce_recruitment\RecruitingManagerInterface
   */
  protected $recruitingManager;

  /**
   * Constructs a new RecruitingCodeController object.
   *
   * @param \Drupal\commerce_recruitment\RecruitingManagerInterface $recruiting_manager
   *   The recruiting service.
   */
  public function __construct(RecruitingManagerInterface $recruiting_manager) {
    $this->recruitingManager = $recruiting_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('commerce_recruitment.manager')
    );
  }

  /**
   * Decrypt recruiting url and redirect to product.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to product.
   */
  public function code($recruiting_code) {
    try {
      $recruiting_session = $this->recruitingManager->getRecruitingSessionFromCode($recruiting_code);
      $config = $recruiting_session->getRecruitingConfig();
      $product = $config->getProduct();
      if ($product !== NULL) {
        $route_name = 'entity.' . $product->getEntityTypeId() . '.canonical';
        return $this->redirect($route_name, [$product->getEntityTypeId() => $product->id()]);
      }
      else {
        // Default redirect on error.
        return $this->redirect('<front>');
      }
    }
    catch (\Throwable $e) {
      $this->getLogger('commerce_recruitment')->error($e->getMessage());
      $this->messenger()->addError($this->t("Invalid Code. Please contact us."));
      return $this->redirect('<front>');
    }

  }

}
