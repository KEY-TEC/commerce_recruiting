<?php

namespace Drupal\commerce_recruiting\Plugin\Block;

use Drupal\commerce_recruiting\CampaignManagerInterface;
use Drupal\commerce_recruiting\Code;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Recruiter' block.
 *
 * @Block(
 *  id = "commerce_recruiting_recruiter",
 *  admin_label = @Translation("Recruiter campaigns sharing link block"),
 *  context = {
 *    "user" = @ContextDefinition("entity:user", required = TRUE)
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
   *   The campaign manager.
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
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'include_unspecified_recruiter_campaigns' => 0,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['include_unspecified_recruiter_campaigns'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Include general campaigns (no recruiter specified)'),
      '#description' => $this->t('This block shows recruiter specific campaigns for the current user. Activate this checkbox to also include campaigns that have no recruiter specified.'),
      '#default_value' => $this->configuration['include_unspecified_recruiter_campaigns'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['include_unspecified_recruiter_campaigns'] = $form_state->getValue('include_unspecified_recruiter_campaigns');
  }

  /**
   * Returns the block build array campaigns of the current user.
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

    $recruiter_code = $this->getContextValue('user')->id();
    $user = User::load($recruiter_code);
    if ($user->hasField('code') && !empty($user->code->value)) {
      $recruiter_code = $user->code->value;
    }

    foreach ($campaigns as $campaign) {
      $build['#campaigns'][$campaign->id()]['entity'] = $campaign;
      $build['#campaigns'][$campaign->id()]['name'] = $campaign->getName();

      /* @var \Drupal\commerce_recruiting\Entity\CampaignOptionInterface $option */
      $options = $campaign->getOptions();
      foreach ($options as $option) {
        if (empty($option->getProduct())) {
          // Skip this option in case the product is missing (e.g. deleted).
          continue;
        }

        $url = Code::create($option->getCode(), $recruiter_code)->url()->toString();
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

      if ($this->configuration['include_unspecified_recruiter_campaigns']) {
        $this->campaigns = array_merge($this->campaigns, $this->campaignManager->findRecruiterCampaigns());
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

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
