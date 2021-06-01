<?php
/**
 * @file
 * Contains \Drupal\samhsa_pep_plugins\Plugin\views\field\DisplayModeField.
 */

namespace Drupal\samhsa_pep_plugins\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\Core\Url;
use Drupal\Component\Serialization\Json;
use Drupal\node\Entity;
use Drupal\Core\Form\FormStateInterface;

/**
 * Field handler to generate Links perform actions on pre-analysis and analysis forms.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("display_mode_field")
 */
class DisplayModeField extends FieldPluginBase {

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
    $product = $entity->getProduct();
    $pep_product_type = $product->get('field_pep_product_type')->value;
    $stock = \Drupal::service('samhsa_pep_stock.pep_stock_utility')->getStock($entity);

    if($pep_product_type == 'download_only'){
        $output = 'Download Only';
    } else if($pep_product_type == 'order_only' || $pep_product_type == 'download_order') {
        $output = 'External';
    } else if($pep_product_type == 'samhsa_only') {
        $output = 'Internal';
    } else {
        $output = 'None';
    }
    return $output;
  }

}