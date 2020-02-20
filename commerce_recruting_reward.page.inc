<?php

/**
 * @file
 * Contains commerce_recruiting_reward.page.inc.
 *
 * Page callback for reward entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for reward templates.
 *
 * Default template: commerce_recruitment_reward.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_commerce_recruitment_reward(array &$variables) {
  // Fetch reward entity.
  $commerce_recruitment_reward = $variables['elements']['#commerce_recruitment_reward'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
