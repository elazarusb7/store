<?php

namespace Drupal\samhsa_pep_cart\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class CartSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pep_cart_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $value = $this->config('samhsa_pep_cart.settings')
      ->get('combine_pep_cart');
    $form['combine_pep_cart'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Combine order items containing the same product variation'),
      '#default_value' => $value,
    ];

    $value = $this->config('samhsa_pep_cart.settings')
      ->get('max_quantity');
    $form['max_quantity'] = [
      '#type' => 'number',
      '#title' => $this
        ->t('Shopping cart max allowed input'),
      '#default_value' => $value,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('samhsa_pep_cart.settings')
      ->set('combine_pep_cart', $values['combine_pep_cart'])
      ->set('max_quantity', $values['max_quantity'])
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['samhsa_pep_cart.settings'];
  }

}
