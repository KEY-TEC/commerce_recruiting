<?php

namespace Drupal\commerce_recruitment\Plugin\Block;

use Drupal\commerce_recruitment\Resolver\ChainRecruitingConfigResolverInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\commerce_recruitment\Encryption;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Sharing Link' block.
 *
 * @Block(
 *  id = "commerce_recruitment_link_sharing_block",
 *  admin_label = @Translation("Product link sharing block"),
 *  context = {
 *    "user" = @ContextDefinition("entity:user", required = FALSE)
 *  }
 * )
 */
class SharingLink extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The recruiting config chain resolver.
   * @var
   */
  protected $chainRecruitingConfigResolver;

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
   * @param \Drupal\commerce_recruitment\Resolver\ChainRecruitingConfigResolverInterface $chain_resolver
   *   The recruiting config chain resolver.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager, ChainRecruitingConfigResolverInterface $chain_resolver) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
    $this->chainRecruitingConfigResolver = $chain_resolver;
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
      $container->get('commerce_recruitment.chain_recruiting_config_resolver')
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
    $result = $this->chainRecruitingConfigResolver->resolve();

    $build = [];
    $build['#theme'] = 'sharing_link';

    /** @var \Drupal\user\UserInterface $user */
    $user = $this->getContextValue('user');
    if (!empty($parent) && !empty($user)) {
      $language = $this->languageManager->getCurrentLanguage();
      $uid = $user->id();
      $pid = $parent->id();
      $entity_type = $parent->getEntityType()->id();
      $values = [$uid, $pid, $entity_type];
      $code = Encryption::encrypt(implode(';', $values));
      if (!empty($code)) {
        $build['recruiting']['#markup'] = Url::fromRoute('commerce_recruitment.recruiting_url', ['recruiting_code' => $code], ['absolute' => TRUE, 'language' => $language])->toString();
      }
    }
    return $build;
  }

  /**
   * Checks access. Don't show this block if user is anonymous.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
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
