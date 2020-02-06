<?php

namespace Drupal\commerce_recruitment\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\commerce_recruitment\Encryption;

/**
 * Provides a 'Sharing Link' block.
 *
 * @Block(
 *  id = "sharing_link",
 *  admin_label = @Translation("Sharing link"),
 *  context = {
 *    "product" = @ContextDefinition("entity:commerce_product", required = FALSE),
 *    "product_bundle" = @ContextDefinition("entity:commerce_product_bundle", required = FALSE),
 *    "user" = @ContextDefinition("entity:user", required = FALSE)
 *  }
 * )
 */
class SharingLink extends BlockBase {

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
      $language = \Drupal::languageManager()->getCurrentLanguage();
      $uid = $user->id();
      $pid = $parent->id();
      $entity_type = $parent->getEntityType()->id();
      if ($entity_type == 'commerce_product') {
        $type = 'p';
      }
      elseif ($entity_type == 'commerce_product_bundle') {
        $type = 'pb';
      }
      else {
        $type = NULL;
      }
      $values = [$uid, $pid, $type];
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
