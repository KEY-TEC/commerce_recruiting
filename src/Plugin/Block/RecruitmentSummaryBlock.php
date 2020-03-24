<?php

namespace Drupal\commerce_recruiting\Plugin\Block;

use Drupal\commerce_recruiting\CampaignManagerInterface;
use Drupal\commerce_recruiting\RecruitmentManagerInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Recruitment Summary' block.
 *
 * @Block(
 *  id = "commerce_recruiting_summary",
 *  admin_label = @Translation("Recruitment Summary block"),
 *  context = {
 *    "user" = @ContextDefinition("entity:user", required = FALSE)
 *  }
 * )
 */
class RecruitmentSummaryBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * The recruitment manager.
   *
   * @var \Drupal\commerce_recruiting\RecruitmentManagerInterface
   */
  private $recruitmentManager;

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
   * @param \Drupal\commerce_recruiting\RecruitmentManagerInterface $recruitment_manager
   *   The recruitment manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager, RouteMatchInterface $route, CampaignManagerInterface $campaign_manager, RecruitmentManagerInterface $recruitment_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
    $this->campaignManager = $campaign_manager;
    $this->route = $route;
    $this->recruitmentManager = $recruitment_manager;
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
      $container->get('commerce_recruiting.recruitment_manager')
    );
  }

  /**
   * Returns the recruitment summary block build.
   *
   * @return array
   *   The build array.
   */
  public function build() {
    $campaigns = $this->findCampaigns();
    $user = $this->getContextValue('user');
    $summaries = [];
    foreach ($campaigns as $campaign) {
      foreach (
        [
        'accepted' => 'accepted',
         'created' => 'pending',
        ] as $state => $key_name) {
        /* @var \Drupal\commerce_recruiting\Entity\CampaignInterface $campaign */
        $summary = $this->recruitmentManager->getRecruitmentSummaryByCampaign($campaign, $state, $user);
        if ($summary->hasResults()) {
          $summaries[$campaign->id()][$key_name] = $summary;
        }
      }
    }
    return  ['#theme' => 'recruitment_summary', '#summaries' => $summaries];
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
    if (empty($this->campaigns)) {
      $user = $this->getContextValue('user');
      $global_campaigns = $this->campaignManager->findRecruiterCampaigns();
      $assigned_campaigns = $this->campaignManager->findRecruiterCampaigns($user);
      $this->campaigns = array_merge($global_campaigns, $assigned_campaigns);
    }
    return $this->campaigns;
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

    return parent::blockAccess($account);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
