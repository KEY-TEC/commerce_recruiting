<?php

namespace Drupal\commerce_recruitment\Plugin\Block;

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
 *    "product" = @ContextDefinition("entity:commerce_product", required = FALSE),
 *    "product_bundle" = @ContextDefinition("entity:commerce_product_bundle", required = FALSE),
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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('language_manager')
    );
  }

  /**
   * Returns the block build array with a encrypted recruiting code.
   *
   * Recruiting code containing:
   *  - current uid
   *  - current entity id
   *  - current entity type (product or product bundle).
   *
   * @return array
   *   The build array.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function build() {
    $build = [];
    $build['#theme'] = 'sharing_link';
    /** @var \Drupal\commerce_product\Entity\ProductInterface $parent */
    $parent = $this->getContextValue('product');
    if ($parent === NULL) {
      /** @var \Drupal\commerce_product_bundle\Entity\BundleInterface $parent */
      $parent = $this->getContextValue('product_bundle');
    }
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
