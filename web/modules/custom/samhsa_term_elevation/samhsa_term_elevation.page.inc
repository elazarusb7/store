<?php

/**
 * @file
 * Contains samhsa_term_elevation.page.inc.
 *
 * Page callback for SAHMSA Term Elevation entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for SAHMSA Term Elevation templates.
 *
 * Default template: samhsa_term_elevation.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_samhsa_term_elevation(array &$variables) {
  // Fetch SamhsaTermElevation Entity Object.
  $samhsa_term_elevation = $variables['elements']['#samhsa_term_elevation'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
