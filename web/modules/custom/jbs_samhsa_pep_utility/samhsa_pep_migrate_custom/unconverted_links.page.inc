<?php

/**
 * @file
 * Contains unconverted_links.page.inc..
 *
 * Page callback for Unconverted links entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Unconverted links templates.
 *
 * Default template: unconverted_links.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_unconverted_links(array &$variables) {
  // Fetch UnconvertedLinks Entity Object.
  $unconverted_links = $variables['elements']['#unconverted_links'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
