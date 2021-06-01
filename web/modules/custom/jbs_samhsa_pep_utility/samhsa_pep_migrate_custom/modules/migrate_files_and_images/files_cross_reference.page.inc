<?php

/**
 * @file
 * Contains files_cross_reference.page.inc..
 *
 * Page callback for Files cross reference entities.
 */

use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Prepares variables for Files cross reference templates.
 *
 * Default template: files_cross_reference.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_files_cross_reference(array &$variables) {
  // Fetch FilesCrossReference Entity Object.
  $files_cross_reference = $variables['elements']['#files_cross_reference'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
