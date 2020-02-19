<?php

namespace Drupal\commerce_recruiting\Plugin\Block;

use Drupal\commerce_recruiting\CampaignManagerInterface;
use Drupal\commerce_recruiting\Code;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Recruiter' block.
 *
 * @Block(
 *  id = "commerce_recruiting_recruiter",
 *  admin_label = @Translation("Product influencer block"),
 *  context = {
 *    "user" = @ContextDefinition("entity:user", required = FALSE)
 *  }
 * )
 */
class RecruiterBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The campaign manager.
   *
   * @var \Drupal\commerce_recruiting\CampaignManagerInterface
   */
  protected $campaignManager;

  /**
   * Recruiter campaigns.
   *
   * @var \Drupal\commerce_recruiting\Entity\CampaignInterface[]|null
   */
  private $campaigns = [];

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
   * @param \Drupal\commerce_recruiting\CampaignManagerInterface $campaign_manager
   *   The recruiting manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager, CampaignManagerInterface $campaign_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
    $this->campaignManager = $campaign_manager;
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
      $container->get('commerce_recruiting.campaign_manager')
    );
  }

  /**
   * Returns the block build array with a encrypted recruiting code.
   *
   * @return array
   *   The build array.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function build() {
    $campaigns = $this->findCampaigns();

    if (empty($campaigns)) {
      return [];
    }

    foreach ($campaigns as $campaign) {
      $build['#campaigns'][$campaign->id()]['entity'] = $campaign;
      $build['#campaigns'][$campaign->id()]['name'] = $campaign->getName();

      /* @var \Drupal\commerce_recruiting\Entity\CampaignOptionInterface $option */
      $options = $campaign->getOptions();
      foreach ($options as $option) {
        $url = Code::create($option->getCode(), $this->getContextValue('user')->id())->url()->toString();
        $build['#campaigns'][$campaign->id()]['options'][$option->id()]['entity'] = $option;
        $build['#campaigns'][$campaign->id()]['options'][$option->id()]['title'] = $option->getProduct()->getTitle();
        $build['#campaigns'][$campaign->id()]['options'][$option->id()]['url'] = $url;
      }
    }
    $build['#theme'] = 'recruiter_campaigns';
    return $build;
  }

  /**
   * Helper method to find current user's campaigns.
   *
   * @return \Drupal\commerce_recruiting\Entity\CampaignInterface[]|null
   *   List of campaigns or null.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private function findCampaigns() {
    if (empty($this->campaigns)) {
      $user = $this->getContextValue('user');
      if (!empty($user)) {
        $this->campaigns = $this->campaignManager->findRecruiterCampaigns($user);
      }
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

}
