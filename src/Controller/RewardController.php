<?php

namespace Drupal\commerce_recruiting\Controller;

use Drupal\commerce_recruiting\Entity\CampaignInterface;
use Drupal\commerce_recruiting\RewardManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class RewardController.
 */
class RewardController extends ControllerBase {

  /**
   * The reward manager.
   *
   * @var \Drupal\commerce_recruiting\RewardManagerInterface
   */
  protected $rewardManager;

  /**
   * The current account.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $accountProxy;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The request.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * Constructs a new RewardController object.
   *
   * @param \Drupal\commerce_recruiting\RewardManagerInterface $reward_manager
   *   The reward service.
   */
  public function __construct(RewardManagerInterface $reward_manager, AccountProxy $account_proxy, LanguageManagerInterface $language_manager, RequestStack $request_stack) {
    $this->rewardManager = $reward_manager;
    $this->accountProxy = $account_proxy;
    $this->languageManager = $language_manager;
    $this->request = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('commerce_recruiting.reward_manager'),
      $container->get('current_user'),
      $container->get('language_manager'),
      $container->get('request_stack')
    );
  }

  /**
   * Creates a reward for recruitments of the given campaign.
   *
   * @param \Drupal\commerce_recruiting\Entity\CampaignInterface $campaign
   *   The recruitment campaign.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to product.
   */
  public function createReward(CampaignInterface $campaign) {
    try {
      $user = User::load($this->accountProxy->id());
      $reward = $this->rewardManager->createReward($campaign, $user);

      $query = $this->request->getCurrentRequest()->query;
      if ($query->has('destination')) {
        $redirect = $query->get('destination');
        $destination = Url::fromUserInput($redirect);
        if ($destination->isRouted()) {
          // Valid internal path.
          return $this->redirect($destination->getRouteName());
        }
      }

      if (empty($reward)) {
        // No reward was created to redirect to.
        return $this->redirect('<front>');
      }

      return new RedirectResponse($reward->toUrl('canonical', ['language' => $this->languageManager->getCurrentLanguage()])->toString(), 302);;
    }
    catch (\Throwable $e) {
      $this->getLogger('commerce_recruitment')->error($e->getMessage());
      $this->messenger()
        ->addError($this->t("Error while creating reward. Please contact us."));
      return $this->redirect('<front>');
    }
  }

}
