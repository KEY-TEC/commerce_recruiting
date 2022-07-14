<?php

namespace Drupal\commerce_recruiting\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines commerce_recruiting_bonus_resolver annotation object.
 *
 * @Annotation
 */
class RecruitmentBonusResolver extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The description of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

}
