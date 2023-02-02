<?php

namespace Drupal\field_qty_max_limit\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'field_simple_text' formatter.
 *
 * @FieldFormatter(
 *   id = "field_qty_max_limit",
 *   module = "field_qty_max_limit",
 *   label = @Translation("Simple formatter"),
 *   field_types = {
 *     "field_qty_max_limit"
 *   }
 * )
 */
class SimpleTextFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        // We create a render array to produce the desired markup,.
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => round($item->value),
        '#cache' => ['max-age' => 2],
      ];
    }

    return $elements;
  }

}
