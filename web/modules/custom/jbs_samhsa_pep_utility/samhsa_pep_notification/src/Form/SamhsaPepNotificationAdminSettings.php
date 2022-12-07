<?php

namespace Drupal\samhsa_pep_notification\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;

/**
 * Class SamhsaPepPasswordAdminSettings.
 *
 * @package Drupal\samhsa_pep_notification\Form
 */
class SamhsaPepNotificationAdminSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'samhsa_pep_notification_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    global $base_url;
    $config = $this->config('samhsa_pep_notification.settings');

    $form['notification'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('SAMHSA PEP System Use Notification settings'),
    ];
    $form['notification']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Notification Page Title'),
      '#description' => $this->t('What should the title of the notification page be?'),
      '#default_value' => $config->get('title'),
      '#size' => 80,
    ];
    $form['notification']['notification_text'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Notification text'),
      '#default_value' => $config->get('notification_text')['value'],
      '#format'        => $config->get('notification_text')['format'],
      '#description' => $this->t('Provide the notification text.'),
      '#rows' => 12,
    ];
    $form['notification']['frequency'] = [
      '#type' => 'select',
      '#title' => $this->t('Frequency'),
      '#default_value' => $config->get('frequency'),
      '#description' => $this->t('How ofter should users be required to accept the agreement?'),
      '#options' => [
        -1 => $this->t('Only once'),
        0 => $this->t('On every log in'),
        365 => $this->t('Once a year'),
      ],
      '#size' => 1,
    ];
    $form['notification']['submit'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Submit text'),
      '#description' => $this->t('This text will be displayed on the "Submit" button.'),
      '#default_value' => $config->get('submit'),
      '#size' => 80,
    ];
    $form['notification']['success'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Success Message'),
      '#description' => $this->t('What message should be displayed to the users once they accept the terms?'),
      '#default_value' => $config->get('success'),
      '#size' => 80,
    ];
    $form['notification']['failure'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Failure Message'),
      '#description' => $this->t('What message should be displayed to the users if they do not accept the terms?'),
      '#default_value' => $config->get('failure'),
      '#size' => 80,
    ];
    $form['notification']['checkbox_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Checkbox Text'),
      '#description' => $this->t('This text will be displayed next to the "I agree" checkbox.'),
      '#default_value' => $config->get('checkbox_text'),
      '#size' => 80,
    ];

    $all_roles = \Drupal::entityTypeManager()->getStorage('user_role')->loadMultiple();
    foreach ($all_roles as $entity) {
      $role = $entity->getTypedData()->getValue();
      $rid  = $role->id();
      if ($rid != 'anonymous') {
        $roles[$rid] = $role->label();
      }
    }

    $form['notification']['roles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Roles'),
      '#options' => $roles,
      '#description' => $this->t('Select all of the roles that are required to agree.'),
    ];
    if (NULL !== $config->get('roles')) {
      $form['notification']['roles']['#default_value'] = $config->get('roles');
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Config\ImmutableConfig */
    $this->configFactory()->getEditable('samhsa_pep_notification.settings')
      ->set('title', $form_state->getValue('title'))
      ->set('notification_text', $form_state->getValue('notification_text'))
      ->set('submit', $form_state->getValue('submit'))
      ->set('success', $form_state->getValue('success'))
      ->set('failure', $form_state->getValue('failure'))
      ->set('frequency', $form_state->getValue('frequency'))
      ->set('checkbox_text', $form_state->getValue('checkbox_text'))
      ->set('roles', $form_state->getValue('roles'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['samhsa_pep_notification.settings'];
  }

}
