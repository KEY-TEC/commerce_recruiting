<?php

/**
 * @file
 * Contains commerce_recruiting.page.inc.
 *
 * Page callback for recruitment entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for recruitment entity templates.
 *
 * Default template: recruitment.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_recruitment(array &$variables) {
  // Fetch Recruitment Entity Object.
  $recruitment = $variables['elements']['#recruitment'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
