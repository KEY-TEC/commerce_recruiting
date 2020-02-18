<?php

/**
 * @file
 * Contains commerce_recruiting_invoice.page.inc.
 *
 * Page callback for Invoice entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Invoice templates.
 *
 * Default template: commerce_recruiting_invoice.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_commerce_recruiting_invoice(array &$variables) {
  // Fetch Invoice Entity Object.
  $commerce_recruiting_invoice = $variables['elements']['#commerce_recruiting_invoice'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
