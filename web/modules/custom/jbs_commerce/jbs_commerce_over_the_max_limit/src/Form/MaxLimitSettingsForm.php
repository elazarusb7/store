<?php

namespace Drupal\jbs_commerce_over_the_max_limit\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class MaxLimitSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'maxlimit_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /*$elements = drupal_map_assoc(array('pre', 'code'));

    $form['maxlimit_element'] = array(
    '#type' => 'select',
    '#title' => $this->t('Select Max Limit field'),
    '#default_value' => $config->get('element'),
    '#options' => $elements,
    );*/

    $value = $this->config('jbs_commerce_over_the_max_limit.settings')
      ->get('maxlimit_element');
    $form['maxlimit_element'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter Max Limit field machine name here'),
      '#default_value' => $value,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('jbs_commerce_over_the_max_limit.settings')
      ->set('maxlimit_element', $values['maxlimit_element'])
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['jbs_commerce_over_the_max_limit.settings'];
  }

}
