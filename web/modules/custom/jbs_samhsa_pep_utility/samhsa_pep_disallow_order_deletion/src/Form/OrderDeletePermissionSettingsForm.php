<?php

namespace Drupal\samhsa_pep_disallow_order_deletion\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class OrderDeletePermissionSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'samhsa_pep_disallow_order_deletion';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $value = $this->config('samhsa_pep_disallow_order_deletion.settings')
      ->get('disable_order_delete');
    $form['disable_order_delete'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disallow Order Deletion'),
      '#default_value' => $value,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('samhsa_pep_disallow_order_deletion.settings')
      ->set('disable_order_delete', $values['disable_order_delete'])
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['samhsa_pep_disallow_order_deletion.settings'];
  }

}
