<?php

namespace Drupal\commerce_recruiting\Plugin\Block;

use Drupal\commerce_recruiting\CampaignManagerInterface;
use Drupal\commerce_recruiting\RecruitingManagerInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Recruiting Summary' block.
 *
 * @Block(
 *  id = "commerce_recruiting_summary",
 *  admin_label = @Translation("Recruiting Summary block"),
 *  context = {
 *    "user" = @ContextDefinition("entity:user", required = FALSE)
 *  }
 * )
 */
class RecruitingSummaryBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * @var \Drupal\commerce_recruiting\Entity\CampaignInterface[]
   */
  private $campaigns;

  /**
   * The recruiting manager.
   *
   * @var \Drupal\commerce_recruiting\RecruitingManagerInterface
   */
  private $recruitingManager;

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
   * @param \Drupal\commerce_recruiting\CampaignManagerInterface $campaign_manager
   *   The campaign manager.
   * @param \Drupal\commerce_recruiting\RecruitingManagerInterface $recruiting_manager
   *   The recruiting manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager, RouteMatchInterface $route, CampaignManagerInterface $campaign_manager, RecruitingManagerInterface $recruiting_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
    $this->campaignManager = $campaign_manager;
    $this->route = $route;
    $this->recruitingManager = $recruiting_manager;
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
      $container->get('commerce_recruiting.manager')
    );
  }

  /**
   * Returns the block build array with a recruiting url to share.
   *
   * @return array
   *   The build array.
   */
  public function build() {
    $campaigns = $this->findCampaigns();

    $summaries = [];
    foreach ($campaigns as $campaign) {
      $summary = $this->recruitingManager->recruitingSummaryByCampaign($campaign);
      $summaries[] = ['#theme' => 'recruiting_summary', 'summary' => $summary];
    }
    return $summaries;
  }

  /**
   * Helper method to find the current matching campaign.
   *
   * @return \Drupal\commerce_recruiting\Entity\CampaignInterface|mixed|null
   *   The campaign or null.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private function findCampaigns() {
    if (empty($this->campaign)) {
      $user = $this->getContextValue('user');
      $this->campaigns = $this->campaignManager->findRecruiterCampaigns($user);
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

    if (count($this->findCampaigns()) == 0) {
      return AccessResult::forbidden();
    }

    return parent::blockAccess($account);
  }

}
