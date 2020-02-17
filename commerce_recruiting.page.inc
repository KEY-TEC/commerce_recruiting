<?php

/**
 * @file
 * Contains commerce_recruiting.page.inc.
 *
 * Page callback for Recruiting entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Recruiting entity templates.
 *
 * Default template: recruiting.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_recruiting(array &$variables) {
  // Fetch Recruiting Entity Object.
  $recruiting = $variables['elements']['#recruiting'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
