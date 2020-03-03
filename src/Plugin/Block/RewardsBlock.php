<?php

namespace Drupal\commerce_recruiting\Plugin\Block;

use Drupal\commerce_recruiting\CampaignManagerInterface;
use Drupal\commerce_recruiting\RewardManagerInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Recruitment Rewards' block.
 *
 * @Block(
 *  id = "commerce_recruiting_rewards",
 *  admin_label = @Translation("Recruitment Rewards block"),
 *  context = {
 *    "user" = @ContextDefinition("entity:user", required = FALSE)
 *  }
 * )
 */
class RewardsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The route.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $route;

  /**
   * The campaign manager.
   *
   * @var \Drupal\commerce_recruiting\CampaignManagerInterface
   */
  protected $campaignManager;


  /**
   * Loaded campaign.
   *
   * @var \Drupal\commerce_recruiting\Entity\RewardInterface[]
   */
  private $rewards = [];

  /**
   * The reward manager.
   *
   * @var \Drupal\commerce_recruiting\RewardManagerInterface
   */
  private $rewardManager;

  /**
   * Constructs a new RewardsBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route
   *   The current route.
   * @param \Drupal\commerce_recruiting\CampaignManagerInterface $campaign_manager
   *   The campaign manager.
   * @param \Drupal\commerce_recruiting\RewardManagerInterface $reward_manager
   *   The reward manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager, RouteMatchInterface $route, CampaignManagerInterface $campaign_manager, RewardManagerInterface $reward_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
    $this->campaignManager = $campaign_manager;
    $this->route = $route;
    $this->rewardManager = $reward_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('language_manager'),
      $container->get('current_route_match'),
      $container->get('commerce_recruiting.campaign_manager'),
      $container->get('commerce_recruiting.reward_manager')
    );
  }

  /**
   * Returns the rewards block build.
   *
   * @return array
   *   The build array.
   */
  public function build() {
    $rewards = $this->findRewards();
    return  ['#theme' => 'recruitment_rewards', '#rewards' => $rewards];;
  }

  /**
   * Helper method to lazy load rewards by current user.
   *
   * @return \Drupal\commerce_recruiting\Entity\RewardInterface|\Drupal\commerce_recruiting\Entity\RewardInterface[]
   *   List of rewards.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private function findRewards() {
    if (!empty($this->rewards)) {
      return $this->rewards;
    }
    else {
      $user = $this->getContextValue('user');
      $this->rewards = $this->rewardManager->findRewards($user);
      return $this->rewards;
    }
  }

  /**
   * Checks access. Don't show this block if user is anonymous.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account.
   *
   * @return \Drupal\Core\Access\AccessResult|\Drupal\Core\Access\AccessResultForbidden
   *   The access result.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function blockAccess(AccountInterface $account) {
    /** @var \Drupal\user\UserInterface $user */
    $user = $this->getContextValue('user');
    if ($user->isAnonymous()) {
      return AccessResult::forbidden();
    }
    if (count($this->findRewards()) === 0) {
      return AccessResult::forbidden();
    }
    return parent::blockAccess($account);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
