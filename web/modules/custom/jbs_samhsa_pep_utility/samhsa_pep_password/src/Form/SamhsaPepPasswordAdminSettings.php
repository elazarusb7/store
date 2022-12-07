<?php

namespace Drupal\samhsa_pep_password\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;

/**
 * Class SamhsaPepPasswordAdminSettings.
 *
 * @package Drupal\samhsa_pep_password\Form
 */
class SamhsaPepPasswordAdminSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'samhsa_pep_password_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    global $base_url;
    $config = $this->config('samhsa_pep_password.settings');

    $form['password_length'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Password length'),
    ];
    $form['password_length']['length_enforce'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enforce minimum password length?'),
      '#default_value' => $config->get('length_enforce'),
    ];
    $form['password_length']['length_min_user'] = [
      '#type' => 'number',
      '#min' => 0,
      '#title' => $this->t('Minimum number of characters for common users'),
      '#default_value' => $config->get('length_min_user'),
      '#size' => 3,
      '#field_suffix' => $this->t('character(s)'),
    ];
    $form['password_length']['length_min_admin'] = [
      '#type' => 'number',
      '#min' => 0,
      '#title' => $this->t('Minimum number of characters for privileged users'),
      '#default_value' => $config->get('length_min_admin'),
      '#size' => 2,
      '#field_suffix' => $this->t('character(s)'),
    ];

    $all_roles = \Drupal::entityTypeManager()->getStorage('user_role')->loadMultiple();
    foreach ($all_roles as $entity) {
      $role = $entity->getTypedData()->getValue();
      $rid  = $role->id();
      if ($rid != 'anonymous' && $rid != 'authenticated') {
        $roles[$rid] = $role->label();
      }
    }

    $form['password_length']['admin_roles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Select privileged roles'),
      '#options' => $roles,
    ];
    if (NULL !== $config->get('admin_roles')) {
      $form['password_length']['admin_roles']['#default_value'] = $config->get('admin_roles');
    }

    $form['password_characters'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Password complexity'),
    ];
    $form['password_characters']['character_enforce'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Require a minimum use of each type of character in a password?'),
      '#default_value' => $config->get('length_enforce'),
    ];
    $form['password_characters']['character_upper_min'] = [
      '#type' => 'number',
      '#min' => 0,
      '#title' => $this->t('Minimum number of Uppercase characters'),
      '#default_value' => $config->get('character_upper_min'),
      '#size' => 3,
      '#field_suffix' => $this->t('character(s)'),
    ];
    $form['password_characters']['character_lower_min'] = [
      '#type' => 'number',
      '#min' => 0,
      '#title' => $this->t('Minimum number of Lowercase characters'),
      '#default_value' => $config->get('character_lower_min'),
      '#size' => 3,
      '#field_suffix' => $this->t('character(s)'),
    ];
    $form['password_characters']['character_numeric_min'] = [
      '#type' => 'number',
      '#min' => 0,
      '#title' => $this->t('Minimum number of Numeric characters'),
      '#default_value' => $config->get('character_numeric_min'),
      '#size' => 3,
      '#field_suffix' => $this->t('character(s)'),
    ];
    $form['password_characters']['character_special_min'] = [
      '#type' => 'number',
      '#min' => 0,
      '#title' => $this->t('Minimum number of Special characters'),
      '#default_value' => $config->get('character_special_min'),
      '#size' => 3,
      '#field_suffix' => $this->t('character(s)'),
    ];

    $form['password_lifetime'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Password lifetime'),
    ];
    $form['password_lifetime']['lifetime_min_enforce'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enforce Minimum password lifetime?'),
      '#description' => $this->t('Password cannot be changed more often that this.'),
      '#default_value' => $config->get('lifetime_min_enforce'),
    ];
    $form['password_lifetime']['lifetime_min'] = [
      '#type' => 'number',
      '#min' => 0,
      '#title' => $this->t('Minimum password lifetime'),
      '#default_value' => $config->get('lifetime_min'),
      '#size' => 3,
      '#field_suffix' => $this->t('day(s)'),
    ];
    $form['password_lifetime']['lifetime_max_enforce'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enforce Maximum password lifetime?'),
      '#description' => $this->t('Password will automatically expire after this period and must be reset.'),
      '#default_value' => $config->get('lifetime_max_enforce'),
    ];
    $form['password_lifetime']['lifetime_max'] = [
      '#type' => 'number',
      '#min' => 0,
      '#title' => $this->t('Maximum password lifetime'),
      '#default_value' => $config->get('lifetime_max'),
      '#size' => 3,
      '#field_suffix' => $this->t('day(s)'),
    ];
    $form['password_lifetime']['cron_threshold'] = [
      '#type' => 'number',
      '#min' => 0,
      '#title' => $this->t('Maximum number of users to process in a single cron pass'),
      '#default_value' => $config->get('cron_threshold'),
      '#size' => 3,
      '#description' => $this->t('Use this setting to throttle the number of users processed at a time.  Helpful for reducing strain on system resources with large user populations.'),
      '#field_suffix' => $this->t('user(s)'),
    ];

    $form['password_reuse'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Password reuse'),
    ];
    $form['password_reuse']['password_reuse_enforce'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enforce password reuse restriction?'),
      '#default_value' => $config->get('lifetime_min_enforce'),
    ];
    $form['password_reuse']['password_reuse_count'] = [
      '#type' => 'number',
      '#min' => 0,
      '#title' => $this->t('Prohibit the reuse of at least this many generations of passwords'),
      '#default_value' => $config->get('password_reuse_count'),
      '#size' => 3,
      '#field_suffix' => $this->t('password(s)'),
    ];

    $form['password_pattern'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Password changed characters'),
      '#description' => 'Note that this validation only applies when a user is changing their password and must enter their existing password to do so.  In any other instance, such as a password reset email link or an administrator setting another user\'s password, the validation does not apply.',
    ];
    $form['password_pattern']['password_pattern_enforce'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Prohibit reuse of identical characters?'),
      '#default_value' => $config->get('password_pattern_enforce'),
    ];
    $form['password_pattern']['password_pattern_yield'] = [
      '#type' => 'number',
      '#min' => 0,
      '#title' => $this->t('Minimum percentage of characters which must be unique between passwords'),
      '#default_value' => $config->get('password_pattern_yield'),
      '#size' => 3,
      '#field_suffix' => $this->t('%'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory()->getEditable('samhsa_pep_password.settings')
      ->set('length_enforce', $form_state->getValue('length_enforce'))
      ->set('length_min_user', $form_state->getValue('length_min_user'))
      ->set('length_min_admin', $form_state->getValue('length_min_admin'))
      ->set('character_enforce', $form_state->getValue('character_enforce'))
      ->set('character_upper_min', $form_state->getValue('character_upper_min'))
      ->set('character_lower_min', $form_state->getValue('character_lower_min'))
      ->set('character_numeric_min', $form_state->getValue('character_numeric_min'))
      ->set('character_special_min', $form_state->getValue('character_special_min'))
      ->set('lifetime_min_enforce', $form_state->getValue('lifetime_min_enforce'))
      ->set('lifetime_min', $form_state->getValue('lifetime_min'))
      ->set('lifetime_max_enforce', $form_state->getValue('lifetime_max_enforce'))
      ->set('lifetime_max', $form_state->getValue('lifetime_max'))
      ->set('password_reuse_enforce', $form_state->getValue('password_reuse_enforce'))
      ->set('password_reuse_count', $form_state->getValue('password_reuse_count'))
      ->set('password_pattern_enforce', $form_state->getValue('password_pattern_enforce'))
      ->set('password_pattern_yield', $form_state->getValue('password_pattern_yield'))
      ->set('cron_threshold', $form_state->getValue('cron_threshold'))
      ->set('admin_roles', $form_state->getValue('admin_roles'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['samhsa_pep_password.settings'];
  }

}
