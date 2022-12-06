<?php

namespace Drupal\samhsa_term_elevation\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a route controller for watches autocomplete form elements.
 */
class AutoCompleteController extends ControllerBase {

  /**
   * Handler for autocomplete request.
   */
  public function handleAutocomplete(Request $request) {
    $string = $request->query->get('q');
    $matches = \Drupal::service('samhsa_te_solr_connections')->searchString($string);
    return new JsonResponse($matches);
  }

}
