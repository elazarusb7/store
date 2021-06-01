<?php

namespace Drupal\samhsa_pep_admin_orders_helper\Plugin\views\field;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("yes_no_bulk_order_views_field")
 */
class YesNoBulkOrderViewsField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Do nothing -- to override the parent query.
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
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $result = NULL;
    if (@$values->_relationship_entities['order_id'] instanceof OrderInterface) {
      $order = $values->_relationship_entities['order_id'];
      $type = $order->get('type')->getValue()[0]['target_id'];
      if ($type == 'samhsa_publication_ob') {
        $result = 'Yes';
      }
      else {
        $result = 'No';
      }
    }
    return $result;
  }

}
