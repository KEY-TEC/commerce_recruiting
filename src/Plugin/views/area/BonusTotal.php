<?php

namespace Drupal\commerce_recruiting\Plugin\views\area;

use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_recruiting\RecruitmentManagerInterface;
use Drupal\views\Plugin\views\area\AreaPluginBase;
use Drupal\views\Plugin\views\argument\NumericArgument;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines an order total area handler.
 *
 * Shows the order total field with its components listed in the footer of a
 * View.
 *
 * @ingroup views_area_handlers
 *
 * @ViewsArea("recruit_a_friend_bonus_total")
 */
class BonusTotal extends AreaPluginBase {

  /**
   * The recruitment manager.
   *
   * @var \Drupal\commerce_recruiting\RecruitmentManagerInterface
   */
  protected $recruitmentManager;

  /**
   * Constructs a new BonusTotal instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\commerce_recruiting\RecruitmentManagerInterface $recruitment_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RecruitmentManagerInterface $recruitment_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

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
      $container->get('commerce_recruiting.recruitment_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['empty']['#description'] = $this->t("Even if selected, this area handler will never render if a valid order cannot be found in the View's arguments.");
  }

  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE) {
    if (!$empty || !empty($this->options['empty'])) {
      foreach ($this->view->argument as $name => $argument) {
        // First look for an order_id argument.
        if (!$argument instanceof NumericArgument) {
          continue;
        }
        if ($name = 'user_id') {
          $total_bonus = $this->recruitmentManager->getTotalBonusPerUser($argument->getValue());
          if ($total_bonus !== NULL) {
            $output = [
              '#type' => 'price',
              '#label' => 'hidden',
              '#markup' => $total_bonus,
            ];
            return $output;
          }
        }
      }
    }
    return [];
  }

}
