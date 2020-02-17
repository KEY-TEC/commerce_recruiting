<?php

namespace Drupal\commerce_recruiting\Controller;

use Drupal\commerce_recruiting\CampaignManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RecruitingCodeController.
 */
class RecruitingCodeController extends ControllerBase {

  /**
   * The campaign service.
   *
   * @var \Drupal\commerce_recruiting\CampaignManagerInterface
   */
  protected $campaignManager;

  /**
   * Constructs a new RecruitingCodeController object.
   *
   * @param \Drupal\commerce_recruiting\CampaignManagerInterface $campaign_manager
   *   The recruiting service.   *.
   */
  public function __construct(CampaignManagerInterface $campaign_manager) {
    $this->campaignManager = $campaign_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('commerce_recruiting.campaign_manager')
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
      $recruiting_session = $this->campaignManager->getSessionFromCode($recruiting_code);
      $config = $recruiting_session->getCampaignOption();
      $product = $config->getProduct();
      $route_name = 'entity.' . $product->getEntityTypeId() . '.canonical';
      return $this->redirect($route_name, [$product->getEntityTypeId() => $product->id()]);
    }
    catch (\Throwable $e) {
      $this->getLogger('commerce_recruiting')->error($e->getMessage());
      $this->messenger()
        ->addError($this->t("Invalid Code. Please contact us."));
      return $this->redirect('<front>');
    }

  }

}
