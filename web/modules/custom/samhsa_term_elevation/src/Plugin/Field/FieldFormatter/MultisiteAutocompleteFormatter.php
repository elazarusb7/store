<?php

namespace Drupal\samhsa_term_elevation\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'MultisiteAutocompleteFormatter' formatter.
 *
 * @FieldFormatter(
 *   id = "multisite_autocomplete_formatter",
 *   label = @Translation("Multisite Autocomplete Formatter"),
 *   field_types = {
 *     "multisite_autocomplete"
 *   }
 * )
 */
class MultisiteAutocompleteFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#type' => 'markup',
        '#markup' => $item->value
      ];
    }
    return $elements;
  }

}
