<?php

namespace Drupal\field_qty_max_limit\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'field_qty_max_limit' widget.
 *
 * @FieldWidget(
 *   id = "field_qty_max_limit_number",
 *   module = "field_qty_max_limit",
 *   label = @Translation("Number Field"),
 *   field_types = {
 *     "field_qty_max_limit"
 *   }
 * )
 */
class TextWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $value = isset($items[$delta]->value) ? $items[$delta]->value : '1';
    $element += [
      '#type' => 'number',
      '#default_value' => $value,
      '#size' => 7,
      '#maxlength' => 7,
      '#description' => $this->t('Please enter a positive number'),
      '#element_validate' => [
        [$this, 'validate'],
      ],
    ];
    return ['value' => $element];
  }

  /**
   * Validate the color number field.
   */
  public function validate($element, FormStateInterface $form_state) {
    $value = $element['#value'];
    if (strlen($value) === 0) {
      $form_state->setValueForElement($element, '');
      return;
    }
    if (!is_numeric($value) || $value < 1) {
      $form_state->setError($element, $this->t('Max Limit must positive number, greater than 0.'));
    }
  }

}
