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
  public function defaultConfiguration() {
    return [
        'bonus_quantity_multiplication' => FALSE,
      ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['bonus_quantity_multiplication'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Multiply the bonus by quantity in order'),
      '#description' => $this->t('The bonus will be multiplied by the quantity of the product in the order. If this is disabled, the bonus will be applied only once.'),
      '#default_value' => $this->configuration['bonus_quantity_multiplication'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $this->configuration['bonus_quantity_multiplication'] = $values['bonus_quantity_multiplication'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function resolveBonus(CampaignOptionInterface $option, OrderItemInterface $order_item) {
    switch ($option->getBonusMethod()) {
      case CampaignOptionInterface::RECRUIT_BONUS_METHOD_FIX:
        $bonus = $option->getBonus();
        break;

      case CampaignOptionInterface::RECRUIT_BONUS_METHOD_PERCENT:
        if (!empty($order_item->getUnitPrice()) && !empty($option->getBonusPercent())) {
          $unit_price = $order_item->getUnitPrice()->getNumber() / 100 * $option->getBonusPercent();
          $bonus = new Price($unit_price, $order_item->getUnitPrice()->getCurrencyCode());
        }
        else {
          // Cannot calculate percent because order item misses unit price.
          return null;
        }
        break;

      default:
        throw new InvalidArgumentException("No valid bonus method selected. Method: '" . $this->getBonusMethod() . "'");
    }

    if ($this->configuration['bonus_quantity_multiplication']) {
      $bonus = $bonus->multiply($order_item->getQuantity());
    }

    return $bonus;
  }

}
