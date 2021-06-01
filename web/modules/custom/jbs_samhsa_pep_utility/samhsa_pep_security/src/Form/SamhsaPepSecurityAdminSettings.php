<?php

namespace Drupal\samhsa_pep_security\Form;
use Drupal\Component\Render\FormattableMarkup;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;

/**
 * Class SamhsaPepSecurityAdminSettings.
 *
 * @package Drupal\samhsa_pep_security\Form
 */
class SamhsaPepSecurityAdminSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'samhsa_pep_security_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    global $base_url;
    $config = $this->config('samhsa_pep_security.settings');

    $form['general_settings'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('General settings'),
    ];
      $formatted_description = new FormattableMarkup(
        '<span class="fullname-wrapper">
          @text The time window to check for security violations: the time in seconds the login information is kept to compute the login attempts count.<br />
          A common example could be 20 minutes. After that time, the attempt is deleted from the list, and will never be considered again.
          <ul>
            <li>60 = 1 minute</li>
            <li>900 = 15 minutes</li>
            <li>1200 = 20 minutes</li>
            <li>1800 = 30 minutes</li>
            <li>3600 = 1 hour</li>
          </ul>
        </span>',
        ['@text' => ''] // needs to be here
      );
      $form['general_settings']['track_time'] = [
      '#type' => 'number',
      '#min' => 0,
      '#title' => $this->t('Track time'),
      '#default_value' => $config->get('track_time'),
      '#size' => 3,
      '#description' => $this->t('@message', ['@message' => $formatted_description]),
      '#field_suffix' => $this->t('second(s)'),
      ];
      $form['general_settings']['user_wrong_count'] = [
        '#type' => 'number',
        '#min' => 0,
        '#title' => $this->t('User'),
        '#default_value' => $config->get('user_wrong_count'),
        '#size' => 3,
        '#description' => $this->t('Enter the number of login failures a user is allowed. <br>
            After this amount is reached, the user will be blocked, no matter the host attempting to log in. Use this option carefully on public sites, as an attacker may block your site users. <br>
            The user blocking protection will not disappear and should be removed manually from the <a href=":user">user management</a> interface.', [':user' => $base_url . '/admin/people']),
        '#field_suffix' => $this->t('failed attempts'),
      ];
      $form['general_settings']['restore_blocked_user'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Automatically restore blocked users?'),
        '#description' => $this->t('When failed attempts expire, automatically reset blocked users to <i>Active</i> status.'),
        '#default_value' => $config->get('restore_blocked_user'),
      ];
      $form['general_settings']['clear_flood'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Clear flood table when clearing login attempts?'),
        '#description' => $this->t('When failed attempts expire, automatically clear corresponding entries from Drupal flood table.'),
        '#default_value' => $config->get('restore_blocked_user'),
      ];
      $form['general_settings']['log_level'] = array(
        '#type' => 'radios',
        '#title' => $this->t('Logging level'),
        '#default_value' => 1,
        '#description' => $this->t('Level of logging written to watchdog log.  <i>Normal</i> is recommended for normal Production use.'),
        '#options' => array(0 => t('None'), 1 => t('Normal'), 2 => t('High')),
      );


    $form['notification'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Notification'),
    ];
      $form['notification']['disable_core_login_error'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Disable login failure error message'),
        '#description' => $this->t('Prevents the display of login error messages. <br>
          A user attempting to login will not be aware if the account exists, an invalid user name or password has been submitted, or if the account is blocked. The core messages are also hidden.'),
        '#default_value' => $config->get('disable_core_login_error'),
      ];
      $form['notification']['notice_attempts_available'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Notify the user about the number of remaining login attempts'),
        '#default_value' => $config->get('notice_attempts_available'),
        '#description' => $this->t('The user is notified about the number of remaining login attempts before the account gets blocked. <br>
          Security tip: If you enable this option, try to not disclose as much of your login policies as possible in the message shown on any failed login attempt.'),
      ];
      $form['notification']['last_login_timestamp'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Display last login timestamp'),
        '#description' => $this->t('When a user successfully logs in, a message will display the last time he logged into the site.'),
        '#default_value' => $config->get('last_login_timestamp'),
      ];
      $form['notification']['last_access_timestamp'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Display last access timestamp'),
        '#description' => $this->t('When a user successfully logs in, a message will display the last site access with this account.'),
        '#default_value' => $config->get('last_access_timestamp'),
      ];
      $form['notification']['message']['notice_attempts_message'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Failed login attempt'),
        '#rows' => 2,
        '#default_value' => $config->get('notice_attempts_message'),
        '#description' => $this->t('Enter the message string to be shown if the login fails after the form is submitted.'),
      ];
      $form['notification']['message']['user_blocked'] = [
        '#type' => 'textarea',
        '#rows' => 2,
        '#title' => $this->t('Blocked user by uid'),
        '#default_value' => $config->get('user_blocked'),
        '#description' => $this->t('Enter the message to be shown when a user gets blocked due to enough failed login attempts.'),
      ];
      $form['notification']['tokens'] = [
        '#type' => 'item',
        '#title' => $this->t('Tokens'),
        '#description' => t("<ul><li>%date: The (formatted) date and time of the event.</li><li>%ip: The IP address tracked for this event.</li><li>%username: The username entered in the login form (sanitized).</li><li>%email: If the user exists, this will be the email address.</li><li>%uid: If the user exists, this will be the user uid.</li><li>%site: The name of the site as configured in the administration.</li><li>%uri: The base url of this Drupal site.</li><li>%edit_uri: Direct link to the user (based on the name entered) edit page.</li><li>%hard_block_attempts: Configured maximum attempts before hard blocking the IP address.</li><li>%soft_block_attempts: Configured maximum attempts before soft blocking the IP address.</li><li>%user_block_attempts: Configured maximum login attempts before blocking the user.</li><li>%user_ip_current_count: The total attempts for this user name tracked from this IP address.</li><li>%ip_current_count: The total login attempts tracked from from this IP address.</li><li>%user_current_count: The total login attempts tracked for this user name .</li><li>%tracking_time: The tracking time value: in hours.</li><li>%tracking_current_count: Total tracked events</li><li>%activity_threshold: Value of attempts to detect ongoing attack.</li></ul>"),
      ];

    // Clean event tracking list.
    $form['actions']['clean_tracked_events'] = [
      '#type' => 'submit',
      '#value' => $this->t('Clear event tracking information'),
      '#weight' => 20,
      '#submit' => ['::cleanTrackedEvents'],
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory()->getEditable('samhsa_pep_security.settings')
      ->set('track_time', $form_state->getValue('track_time'))
      ->set('user_wrong_count', $form_state->getValue('user_wrong_count'))
      ->set('restore_blocked_user', $form_state->getValue('restore_blocked_user'))
      ->set('activity_threshold', $form_state->getValue('activity_threshold'))
      ->set('disable_core_login_error', $form_state->getValue('disable_core_login_error'))
      ->set('notice_attempts_available', $form_state->getValue('notice_attempts_available'))
      ->set('last_login_timestamp', $form_state->getValue('last_login_timestamp'))
      ->set('last_access_timestamp', $form_state->getValue('last_access_timestamp'))
      ->set('notice_attempts_message', $form_state->getValue('notice_attempts_message'))
      ->set('user_blocked', $form_state->getValue('user_blocked'))
      ->set('clear_flood', $form_state->getValue('clear_flood'))
      ->set('log_level', $form_state->getValue('log_level'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Submit handler to clean the samhsa_pep_security_track table.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   */
  public function cleanTrackedEvents(array &$form, FormStateInterface $form_state) {
    $count = _samhsa_pep_security_remove_all_events();
    drupal_set_message($this->t('Login Security event track list is now empty. @count item(s) deleted.', ['@count' => $count]));
  }

  public function killSession(array &$form, FormStateInterface $form_state) {
  //use Drupal\user\Entity\User;
    $account = \Drupal\user\Entity\User::load(15);
    //  Argument 1 passed to Drupal\\Core\\Session\\AccountProxy::setAccount()
    // must implement interface Drupal\\Core\\Session\\AccountInterface, null given
    \Drupal::currentUser()->setAccount($account);
    if (\Drupal::currentUser()->isAuthenticated()) {
      $session_manager = \Drupal::service('session_manager');
      $session_manager->delete(\Drupal::currentUser()->id());
    }

  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['samhsa_pep_security.settings'];
  }

}
