<?php

namespace Drupal\commerce_recruiting\Plugin\Commerce\RecruitmentBonusResolver;

use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_recruiting\Entity\CampaignOptionInterface;
use Drupal\Core\Form\FormStateInterface;
use http\Exception\InvalidArgumentException;

/**
 * Plugin implementation of the commerce_recruiting_bonus_resolver.
 *
 * @RecruitmentBonusResolver(
 *   id = "default_bonus_resolver",
 *   label = @Translation("Default Bonus Resolver"),
 *   description = @Translation("Resolves the reward by the campaign option configuration.")
 * )
 */
class DefaultBonusResolver extends RecruitmentBonusResolverPluginBase {

  /**
   * {@inheritdoc}
   */
  public function description() {
    return $this->t('The bonus will be applied as configured in the campaign options below.');
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // No config required here.
    $form['#markup'] = '<p>' . $this->description() . '</p>';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveBonus(CampaignOptionInterface $option, OrderItemInterface $order_item) {
    $campaign = $option->getCampaign();
    switch ($option->getBonusMethod()) {
      case CampaignOptionInterface::RECRUIT_BONUS_METHOD_FIX:
        $bonus = $option->getBonus();
        break;

      case CampaignOptionInterface::RECRUIT_BONUS_METHOD_PERCENT:
        $unit_price = $order_item->getUnitPrice()->getNumber() / 100 * $option->getBonusPercent();
        $bonus = new Price($unit_price, $order_item->getUnitPrice()->getCurrencyCode());
        break;

      default:
        throw new InvalidArgumentException("No valid bonus method selected. Method: '" . $this->getBonusMethod() . "'");
    }

    if ($campaign->hasField('bonus_quantity_multiplication') && $campaign->bonus_quantity_multiplication->value) {
      $bonus = $bonus->multiply($order_item->getQuantity());
    }

    return $bonus;
  }

}
