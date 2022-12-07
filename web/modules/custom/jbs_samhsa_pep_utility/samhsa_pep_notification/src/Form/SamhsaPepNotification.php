<?php

namespace Drupal\samhsa_pep_notification\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\DatabaseExceptionWrapper;

/**
 *
 */
class SamhsaPepNotification extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'samhsa_pep_notification_form';
  }

  /**
   * @return string Title of form page
   */
  public function getTitle() {
    $config = $this->config('samhsa_pep_notification.settings');
    return $config->get('title');
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @return array Form
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('samhsa_pep_notification.settings');

    $form['agree'] = [
      '#prefix' => $config->get('notification_text')['value'],
      '#type'   => 'checkbox',
      '#title'  => $config->get('checkbox_text'),
      '#default_value' => 0,
      '#required' => TRUE,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type'  => 'submit',
      '#value' => $config->get('submit'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('agree') == FALSE) {
      $config = $this->config('samhsa_pep_notification.settings');
      $form_state->setErrorByName('agree', $this->t($config->get('failure')));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    try {
      /** @var \Drupal\Core\Database\Connection */
      $connection = \Drupal::service('database');
      /** @var \Drupal\Core\Database\Transaction $transaction */
      $transaction = $connection->startTransaction();
      $uid = \Drupal::currentUser()->id();
      $id = $connection->merge('samhsa_pep_notification_history')
        ->fields([
          'uid'       => $uid,
          'sid'       => session_id(),
          'timestamp' => \Drupal::time()->getRequestTime(),
        ])
        ->condition('uid', $uid)
        ->execute();
    }
    catch (DatabaseExceptionWrapper $e) {
      $transaction->rollback();
      return FALSE;
    }
    catch (\Exception $e) {
      $transaction->rollback();
      return FALSE;
    }

    \Drupal::messenger()->addMessage($this->t($this->config('samhsa_pep_notification.settings')->get('success')));
    $msg = \Drupal::service('samhsa_pep_utility.pep_utility_functions')->showRequiredTfaMsg($uid);
    if (strlen($msg) > 0) {
      \Drupal::messenger()->addMessage($msg, 'error');
    }

    $form_state->setRebuild(FALSE);
    $url = $this->getRedirectionUrl();
    if ($url) {
      $form_state->setRedirectUrl($url);
    }
  }

  /**
   * Checks the login redirection per role and provides a redirection URL:
   *
   * @return \Drupal\Core\Url
   *   URL to be redirected to.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getRedirectionUrl() {
    // Loads configuration from login_redirect_per_role module.
    $config = $this->config('samhsa_pep_notification.settings');
    $roles = \Drupal::entityTypeManager()->getStorage('user_role')->loadMultiple();
    $redirection_paths = [];
    foreach (array_keys($roles) as $role) {
      $action_key = 'login.' . $role;
      if ($path = $config->get($action_key)) {
        $redirection_paths[$role] = $path['redirect_url'];
      }
    }

    // Default redirection.
    $redirection_url = '/';
    $current_user_roles = \Drupal::currentUser()->getRoles();
    if (isset($current_user_roles[1])) {
      $redirection_url = $redirection_paths[$current_user_roles[1]];
    }
    else {
      // Check for items in cart.
      /** @var Drupal\commerce_order\Entity\Order */
      $cart = \Drupal::service('commerce_cart.cart_provider')
        ->getCarts();
      if (count($cart)) {
        // We have items in a cart so redirect after accepting notification to cart.
        $order_id = key($cart);
        $redirection_url = "/checkout/$order_id/order_information";
      }
    }
    if ($redirection_url) {
      return Url::fromUserInput($redirection_url);
    }
    else {
      return Url::fromUserInput('/user');
    }
  }

}
