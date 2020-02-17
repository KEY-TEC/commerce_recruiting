<?php

namespace Drupal\commerce_recruiting\Plugin\Block;

use Drupal\commerce_recruiting\CampaignManagerInterface;
use Drupal\commerce_recruiting\Code;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Sharing Link' block.
 *
 * @Block(
 *  id = "commerce_recruiting_friend",
 *  admin_label = @Translation("Product link sharing block"),
 *  context = {
 *    "entity" = @ContextDefinition("entity", required = FALSE),
 *    "user" = @ContextDefinition("entity:user", required = FALSE)
 *  }
 * )
 */
class FriendBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * The recruiting manager.
   *
   * @var \Drupal\commerce_recruiting\CampaignManagerInterface
   */
  protected $campaignManager;

  /**
   * Loaded campaign.
   *
   * @var \Drupal\commerce_recruiting\Entity\CampaignInterface|null
   */
  private $campaign;

  /**
   * Constructs a new CartBlock.
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
   * @param \Drupal\commerce_recruiting\CampaignManagerInterface $recruiting_manager
   *   The campaign manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager, RouteMatchInterface $route, CampaignManagerInterface $recruiting_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
    $this->campaignManager = $recruiting_manager;
    $this->route = $route;
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
      $container->get('commerce_recruiting.campaign_manager')
    );
  }

  /**
   * Returns the block build array with a encrypted recruiting code.
   *
   * @return array
   *   The build array.
   */
  public function build() {
    $build = [];
    $build['#theme'] = 'sharing_link';

    /* @var \Drupal\commerce_recruiting\Entity\CampaignInterface $campaign */
    $campaign = $this->findCampaign();

    if (empty($campaign)) {
      // Nothing found.
      return $build;
    }

    /* @var \Drupal\commerce_recruiting\Entity\CampaignOptionInterface $option */
    $option = current($campaign->getOptions());

    $url = Code::create($option->getCode(), $this->getContextValue('user')->id())->url()->toString();
    $build['recruiting_code']['#markup'] = $url;
    return $build;
  }

  /**
   * Helper method to find the current matching campaign.
   *
   * @return \Drupal\commerce_recruiting\Entity\CampaignInterface|mixed|null
   *   The campaign or null.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private function findCampaign() {
    if (empty($this->campaign)) {
      $entity = $this->getContextValue('entity');
      if (!empty($entity)) {
        $campaigns = $this->campaignManager->findCampaigns(NULL, $entity);
      }
      if (empty($campaigns)) {
        $campaigns = $this->campaignManager->findCampaigns();
      }
      if (!empty($campaigns)) {
        $this->campaign = current($campaigns);
      }
    }
    return $this->campaign;
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

    if (empty($this->findCampaign())) {
      return AccessResult::forbidden();
    }

    return parent::blockAccess($account);
  }

}
