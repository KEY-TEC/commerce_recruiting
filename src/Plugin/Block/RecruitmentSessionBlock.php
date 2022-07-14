<?php

namespace Drupal\commerce_recruiting\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Recruitment Session Info' block.
 *
 * Its main purpose is for debugging.
 *
 * @Block(
 *  id = "commerce_recruiting_session_info",
 *  admin_label = @Translation("Recruitment Session Info"),
 *  context = {
 *    "user" = @ContextDefinition("entity:user", required = FALSE)
 *  }
 * )
 */
class RecruitmentSessionBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The recruitment session.
   *
   * @var \Drupal\commerce_recruiting\RecruitmentSessionInterface
   */
  protected $recruitmentSession;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );

    $instance->recruitmentSession = $container->get('commerce_recruiting.recruitment_session');
    return $instance;
  }

  /**
   * Returns the current recruitment session info output.
   *
   * @return array
   *   The build array.
   */
  public function build() {
    $output = [
      '#theme' => 'recruitment_session_info',
      '#recruiter' => $this->recruitmentSession->getRecruiter(),
      '#option' => $this->recruitmentSession->getCampaignOption(),
    ];

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
