<?php

namespace Drupal\samhsa_pep_anonlogout\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides settings for autologout module.
 */
class AnonlogoutSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'samhsa_pep_anonlogout_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    global $base_url;
    $config = $this->config('samhsa_pep_anonlogout.settings');

    $form['anonlogout'] = [
      '#type'   => 'details',
      '#open'   => TRUE,
      '#title'  => $this->t('SAMHSA PEP Anonymous user session settings'),
    ];

    $form['anonlogout']['timeout'] = [
      '#type'           => 'textfield',
      '#title'          => $this->t('Timeout value in seconds'),
      '#default_value'  => $config->get('timeout'),
      '#size'           => 8,
      '#weight'         => -10,
      '#description'    => $this->t('The length of inactivity time, in seconds, before session is cleared.'),
    ];

    $form['anonlogout']['redirect_url'] = [
      '#type'           => 'textfield',
      '#title'          => $this->t('Redirect URL'),
      '#default_value'  => $config->get('redirect_url'),
      '#size'           => 40,
      '#description'    => $this->t('Send users to this URL when session terminates.'),
    ];

    $form['anonlogout']['add_to_cart_message'] = [
      '#type'           => 'textfield',
      '#title'          => $this->t('Add To Cart message'),
      '#default_value'  => $config->get('add_to_cart_message'),
      '#size'           => 120,
      '#description'    => $this->t('Message to display when adding products to the cart.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->configFactory()->getEditable('samhsa_pep_anonlogout.settings')
      ->set('timeout', $values['timeout'])
      ->set('redirect_url', $values['redirect_url'])
      ->set('add_to_cart_message', trim($values['add_to_cart_message']))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['samhsa_pep_anonlogout.settings'];
  }

}
