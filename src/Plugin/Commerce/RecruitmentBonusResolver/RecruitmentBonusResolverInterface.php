<?php

namespace Drupal\commerce_recruiting\Plugin\Commerce\RecruitmentBonusResolver;

use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_recruiting\Entity\CampaignOptionInterface;
use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Interface for commerce_recruiting_bonus_resolver plugins.
 */
interface RecruitmentBonusResolverInterface extends ConfigurableInterface, PluginFormInterface, PluginInspectionInterface {

  /**
   * Returns the translated plugin label.
   *
   * @return string
   *   The translated title.
   */
  public function label();

  /**
   * Returns a description text for this plugin.
   *
   * @return string
   *   The description.
   */
  public function description();

  /**
   * Calculated the bonus for a recruitment.
   *
   * @param \Drupal\commerce_recruiting\Entity\CampaignOptionInterface $option
   *   The campaign option.
   * @param \Drupal\commerce_order\Entity\OrderItemInterface $order_item
   *   The order item.
   *
   * @return \Drupal\commerce_price\Price|null
   *   The bonus or null if bonus cannot be calculated.
   */
  public function resolveBonus(CampaignOptionInterface $option, OrderItemInterface $order_item);

}
