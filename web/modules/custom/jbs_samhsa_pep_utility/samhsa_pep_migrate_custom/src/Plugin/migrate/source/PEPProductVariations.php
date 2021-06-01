<?php

namespace Drupal\samhsa_pep_migrate_custom\Plugin\migrate\source;

use CommerceGuys\Intl\Currency\CurrencyRepository;
use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\d7\FieldableEntity;

/**
 * Gets Commerce 1 commerce_product data from database.
 *
 * @MigrateSource(
 *   id = "pep_product_variation",
 *   source_module = "commerce_product"
 * )
 */
class PEPProductVariations extends FieldableEntity {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'product_id' => t('Product variation ID'),
      'sku' => t('SKU'),
      'title' => t('Title'),
      'type' => t('Type'),
      'language' => t('Language'),
      'status' => t('Status'),
      'created' => t('Created'),
      'changed' => t('Changes'),
      'data' => t('Data'),
      'commerce_price' => t('Price with amount, currency_code and fraction_digits'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['product_id']['type'] = 'integer';
    $ids['product_id']['alias'] = 'p';
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('commerce_product', 'p')->fields('p');
    if (isset($this->configuration['product_variation_type'])) {
      $query->condition('p.type', $this->configuration['product_variation_type']);
    }
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $product_id = $row->getSourceProperty('product_id');
    $revision_id = $row->getSourceProperty('revision_id');
    foreach (array_keys($this->getFields('commerce_product', $row->getSourceProperty('type'))) as $field) {
      $row->setSourceProperty($field, $this->getFieldValues('commerce_product', $field, $product_id, $revision_id));
    }

    //update variaton of the status based on the product status it is associated with
    $product_id = $row->getSourceProperty('product_id');
    $query = 'SELECT n.status
    FROM
    node n 
    INNER JOIN field_data_field_samhsa_product_publication pp
    ON n.nid = pp.entity_id
    WHERE pp.field_samhsa_product_publication_product_id = :product_id';
    $result = $this->getDatabase()->query($query, array(':product_id' => $row->getSourceProperty('product_id')));
    $values = [];
    foreach ($result as $record) {
        $values[] = $record->status;
        \Drupal::logger('samhsa_pep_migrate_custom')->notice($product_id ." / ".$record->status);
    }

    \Drupal::logger('product_status')->notice($query);

    // Include the number of currency fraction digits in the price.
    $currencyRepository = new CurrencyRepository();
    $value = $row->getSourceProperty('commerce_price');
    $currency_code = $value[0]['currency_code'];
    $value[0]['fraction_digits'] = $currencyRepository->get($currency_code)->getFractionDigits();
    $row->setSourceProperty('commerce_price', $value);

    // field_available_quantity
    $components = array(
       'field' => 'commerce_stock_value',
       'table' => 'field_data_commerce_stock',
       'property' => 'stock',
       'idfield' => 'product_id',
    );
    \Drupal::service('set_migrating_values')->simpleField($this, $row, $components);

    return parent::prepareRow($row);
  }

}
