<?php

/**
 * @file
 * Contains commerce_recruiting_camp_option.page.inc.
 *
 * Page callback for Recruiting option entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Recruiting option templates.
 *
 * Default template: commerce_recruiting_camp_option.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_commerce_recruiting_camp_option(array &$variables) {
  // Fetch CampaignOption Entity Object.
  $commerce_recruiting_camp_option = $variables['elements']['#commerce_recruiting_camp_option'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
