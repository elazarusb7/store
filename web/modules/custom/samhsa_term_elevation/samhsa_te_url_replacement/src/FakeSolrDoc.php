<?php

namespace Drupal\samhsa_te_url_replacement;

/**
 * Class FakeSolrDoc.
 *
 * @package Drupal\samhsa_te_development_helper
 */
class FakeSolrDoc {

  protected $fields = [];

  /**
   *
   */
  public function __construct($f) {
    $this->fields = $f;
  }

  /**
   * @return array
   */
  public function getFields(): array {
    return $this->fields;
  }

}
