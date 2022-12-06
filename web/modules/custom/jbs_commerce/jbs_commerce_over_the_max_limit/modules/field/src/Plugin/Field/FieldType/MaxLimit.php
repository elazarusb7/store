<?php

namespace Drupal\field_qty_max_limit\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'field_qty_max_limit' field type.
 *
 * @FieldType(
 *   id = "field_qty_max_limit",
 *   label = @Translation("Max Limit"),
 *   module = "field_qty_max_limit",
 *   default_widget = "field_qty_max_limit_number",
 *   description = @Translation("Max Limit"),
 *   cardinality = 1,
 * )
 */
class MaxLimit extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(
    FieldStorageDefinitionInterface $field_definition
  ) {
    return [
      'columns' => [
        'value' => [
          'type' => 'numeric',
          'size' => 'normal',
          'precision' => 19,
          'scale' => 0,
          'not null' => FALSE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(
    FieldStorageDefinitionInterface $field_definition
  ) {
    $properties['value'] = DataDefinition::create('integer')
      ->setLabel(t('Max Limit'));
    $properties['max_limit'] = DataDefinition::create('integer')
      ->setLabel(t('Max Limit'))
      ->setSetting('max limit', 'summary');

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return $value === NULL;
  }

  /**
   * @inheritdoc
   *
   * @throws \InvalidArgumentException
   *   In case of a invalid max limit value.
   */
  public function setValue($values, $notify = TRUE) {
    if (!is_array($values)) {
      $value = filter_var($values, FILTER_VALIDATE_INT);
      if ($value === FALSE) {
        throw new \InvalidArgumentException('Values passed to the commerce max limit field must be integer');
      }
    }

    // Set the value so it is not recognized as empty by isEmpty() and
    // postSave() is called.
    if (isset($values['value'])) {
      $values['value'] = $values['value'];
    }
    else {
      $values['value'] = 1;
    }
    parent::setValue($values, $notify);
  }

  /**
   * {@inheritdoc}
   */
  public function postSave($update) {
    // Retrieve entity and saved max limit.
    $entity = $this->getEntity();
    $values = $entity->{$this->getFieldDefinition()->getName()}->getValue();
    $values = reset($values);
  }

}
