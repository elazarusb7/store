<?php

namespace Drupal\samhsa_pep_plugins\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\Core\Form\FormStateInterface;

/**
 * Field handler to get Product Pallets separated by comma.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("product_pullets_count_field")
 */
class ProductPalletsCountField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }

  /**
   * @{inheritdoc}
   */
  public function query() {
    // Leave empty to avoid a query on this field.
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['hide_alter_empty'] = ['default' => FALSE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * @{inheritdoc}
   */
  public function render(ResultRow $values) {
    $entity = $values->_entity;
    $pallets = \Drupal::service('samhsa_pep_stock.pep_stock_utility')->lookupPublicationPallets($entity->id());
    $pallets_count = getPalletsCountPerProd($pallets);

    return $pallets_count;
  }

}
