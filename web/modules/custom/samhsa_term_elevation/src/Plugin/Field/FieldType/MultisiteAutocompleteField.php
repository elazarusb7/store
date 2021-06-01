<?php


namespace Drupal\samhsa_term_elevation\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Plugin implementation of the 'multisite_autocomplete' field type.
 *
 * @FieldType(
 *   id = "multisite_autocomplete",
 *   label = @Translation("Multisite Autocomplete Field Type"),
 *   description = @Translation("This field is used to store alpha-numeric values."),
 *   default_widget = "multisite_autocomplete_widget",
 *   default_formatter = "multisite_autocomplete_formatter"
 * )
 */
class MultisiteAutocompleteField extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $definition) {
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Text value'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $definition) {
    $schema = [
      'columns' => [
        'value' => [
          'type' => 'varchar',
          'length' => 255,
        ],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->getValue();
    if (isset($value['value']) && $value['value'] != '') {
      return FALSE;
    }
    return TRUE;
  }

}
