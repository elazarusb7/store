<?php
namespace Drupal\samhsa_term_elevation\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'MultisiteAutocompleteWidget' widget.
 *
 * @FieldWidget(
 *   id = "multisite_autocomplete_widget",
 *   label = @Translation("Multisite Autocomplete Widget"),
 *   field_types = {
 *     "multisite_autocomplete"
 *   }
 * )
 */
class MultisiteAutocompleteWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element['value'] =  [
      '#type' => 'textfield',
      '#title' => 'My multisite autocomplete field',
      '#description' => 'Custom field to be used for alpha-numeric values',
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
      '#empty_value' => '',
      '#weight' => 0,
      '#autocomplete_route_name' => 'samhsa_term_elevation.matcher',
    ];

    return $element;
  }

}
