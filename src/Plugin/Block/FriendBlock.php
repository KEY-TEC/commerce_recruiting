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
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Product sharing link' block.
 *
 * @Block(
 *  id = "commerce_recruiting_friend",
 *  admin_label = @Translation("Product sharing link block"),
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
   * The campaign manager.
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
   * @param \Drupal\commerce_recruiting\CampaignManagerInterface $campaign_manager
   *   The campaign manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager, RouteMatchInterface $route, CampaignManagerInterface $campaign_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
    $this->campaignManager = $campaign_manager;
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
   * Returns the block build array with a recruitment url to share.
   *
   * @return array
   *   The build array.
   */
  public function build() {
    /* @var \Drupal\commerce_recruiting\Entity\CampaignInterface $campaign */
    $campaign = $this->findCampaign();

    if (empty($campaign)) {
      // Nothing found.
      return [];
    }

    $recruiter_code = $this->getContextValue('user')->id();
    $user = User::load($recruiter_code);
    if ($user->hasField('code') && !empty($user->code->value)) {
      $recruiter_code = $user->code->value;
    }

    /* @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $this->getContextValue('entity');
    foreach ($campaign->getOptions() as $option) {
      /* @var \Drupal\commerce_recruiting\Entity\CampaignOptionInterface $option */
      if ($option->getProduct()->id() == $entity->id() && $option->getProduct()->getEntityTypeId() == $entity->getEntityTypeId()) {
        $url = Code::create($option->getCode(), $recruiter_code)->url()->toString();
        $build['#theme'] = 'friend_share_block';
        $build['#share_link'] = $url;
        return $build;
      }
    }

    return [];
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
        $campaigns = $this->campaignManager->findNoRecruiterCampaigns($entity);
      }

      // @todo: campaigns need at least one option with product. A general solution is not supported at this point.
      /*if (empty($campaigns)) {
        $campaigns = $this->campaignManager->findNoRecruiterCampaigns();
      }*/

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
