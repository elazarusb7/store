<?php

namespace Drupal\samhsa_pep_stock\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class SamhsaPepStockConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pep_stock_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $value = $this->config('samhsa_pep_stock.settings')
      ->get('physical_count_error_message');

    $form['physical_count_error_message'] = [
      // '#type' => 'textarea',
      '#type' => 'text_format',
      '#format' => $value['format'],
      '#title' => $this->t('Add error message which will be displayed when physical count is less than allocated quantity'),
      '#default_value' => $value['value'],
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('samhsa_pep_stock.settings')
      ->set('physical_count_error_message', $values['physical_count_error_message'])
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['samhsa_pep_stock.settings'];
  }

}
