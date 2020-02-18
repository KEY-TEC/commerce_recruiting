<?php

namespace Drupal\commerce_recruiting\Controller;

use Drupal\commerce_recruiting\CampaignManagerInterface;
use Drupal\commerce_recruiting\Code;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RecruitingCodeController.
 */
class RecruitingCodeController extends ControllerBase {

  /**
   * The current account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentAccount;

  /**
   * The campaign service.
   *
   * @var \Drupal\commerce_recruiting\CampaignManagerInterface
   */
  protected $campaignManager;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new RecruitingCodeController object.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_account
   *   The current account.
   * @param \Drupal\commerce_recruiting\CampaignManagerInterface $campaign_manager
   *   The recruiting service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(AccountInterface $current_account, CampaignManagerInterface $campaign_manager, MessengerInterface $messenger) {
    $this->campaignManager = $campaign_manager;
    $this->messenger = $messenger;
    $this->currentAccount = $current_account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('commerce_recruiting.campaign_manager'),
      $container->get('messenger')
    );
  }

  /**
   * Decrypt recruiting url and redirect to product.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to product.
   */
  public function code($campaign_code) {
    $code = Code::createFromCode($campaign_code);

    try {
      $recruiter = $this->campaignManager->getRecruiterFromCode($code);
      if ($recruiter->id() == $this->currentAccount->id()) {
        \Drupal::messenger()->addMessage(t('You can not use your own recommendation url.'));
      }
      else {
        $recruiting_session = $this->campaignManager->saveRecruitingSession($code);
      }

      $option = $this->campaignManager->findCampaignOptionFromCode($code);
      $product = $option->getProduct();
      $route_name = 'entity.' . $product->getEntityTypeId() . '.canonical';
      return $this->redirect($route_name, [$product->getEntityTypeId() => $product->id()]);
    }
    catch (\Throwable $e) {
      $this->getLogger('commerce_recruiting')->error($e->getMessage());
      $this->messenger()
        ->addError($this->t("Invalid Code. If you believe this to be an error please contact us."));
      return $this->redirect('<front>');
    }
  }

}
