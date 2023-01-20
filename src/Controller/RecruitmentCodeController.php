<?php

namespace Drupal\commerce_recruiting\Controller;

use Drupal\commerce_recruiting\Code;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller for handling recruiting urls.
 */
class RecruitmentCodeController extends ControllerBase {

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
   * The request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The page cache kill switch.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  protected $pageCacheKillSwitch;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = new static();

    $instance->currentAccount = $container->get('current_user');
    $instance->campaignManager = $container->get('commerce_recruiting.campaign_manager');
    $instance->messenger = $container->get('messenger');
    $instance->request = $container->get('request_stack')->getCurrentRequest();
    $instance->pageCacheKillSwitch = $container->get('page_cache_kill_switch');

    return $instance;
  }

  /**
   * Page callback for route commerce_recruiting.recruitment_url.
   *
   * Decrypts a recruitment url to save the recruitment session
   * and redirects to the product.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to product.
   */
  public function code($campaign_code) {
    // Never cache me.
    $this->pageCacheKillSwitch->trigger();
    $code = Code::createFromCode($campaign_code);

    try {
      $recruiter = $this->campaignManager->getRecruiterFromCode($code);
      $option = $this->campaignManager->findCampaignOptionFromCode($code);

      if ($recruiter->id() == $this->currentAccount->id() && (!$option->getCampaign()->hasField('allow_self_recruit') || !$option->getCampaign()->allow_self_recruit->value)) {
        \Drupal::messenger()->addMessage(t('You can not use your own recommendation url.'));
      }
      else {
        $this->campaignManager->saveRecruitmentSession($code);
      }

      if ($option->hasField('redirect') && !empty($option->redirect->first()->uri)) {
        /** @var \Drupal\Core\Url $url */
        $url = $option->redirect->first()->getUrl();
        $url->setOption('query', $this->request->query->all());
        if ($url->isExternal()) {
          return new TrustedRedirectResponse($url->toString());
        }
        return new RedirectResponse($url->toString());
      }

      $product = $option->getProduct();
      $route_name = 'entity.' . $product->getEntityTypeId() . '.canonical';
      return $this->redirect($route_name,
        [
          $product->getEntityTypeId() => $product->id(),
        ],
        [
          'query' => $this->request->query->all(),
        ]
      );
    }
    catch (\Throwable $e) {
      $this->getLogger('commerce_recruitment')->error($e->getMessage());
      $this->messenger()
        ->addError($this->t("Invalid Code. If you believe this to be an error please contact us."));
      return $this->redirect('<front>');
    }
  }

}
