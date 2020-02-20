<?php

namespace Drupal\commerce_recruiting\Controller;

use Drupal\commerce_recruiting\Entity\CampaignInterface;
use Drupal\commerce_recruiting\RewardManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountProxy;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
   * @var \Drupal\Core\Session\AccountProxy
   */
  private $accountProxy;

  /**
   * Constructs a new RewardController object.
   *
   * @param \Drupal\commerce_recruiting\RewardManagerInterface $reward_manager
   *   The reward service.
   */
  public function __construct(RewardManagerInterface $reward_manager, AccountProxy $account_proxy) {
    $this->rewardManager = $reward_manager;
    $this->accountProxy = $account_proxy;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('commerce_recruiting.reward_manager'),
      $container->get('current_user')
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
      return new RedirectResponse($reward->toUrl()->toString(), 302);;
    }
    catch (\Throwable $e) {
      $this->getLogger('commerce_recruitment')->error($e->getMessage());
      $this->messenger()
        ->addError($this->t("Error while creating reward. Please contact us."));
      return $this->redirect('<front>');
    }
  }

}
