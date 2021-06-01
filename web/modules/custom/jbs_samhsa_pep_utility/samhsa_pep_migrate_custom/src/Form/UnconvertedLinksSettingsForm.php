<?php

/**
 * @file
 * Contains \Drupal\samhsa_pep_migrate_custom\Form\UnconvertedLinksSettingsForm.
 */

namespace Drupal\samhsa_pep_migrate_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class UnconvertedLinksSettingsForm.
 *
 * @package Drupal\samhsa_pep_migrate_custom\Form
 *
 * @ingroup samhsa_pep_migrate_custom
 */
class UnconvertedLinksSettingsForm extends FormBase {
  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'UnconvertedLinks_settings';
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Empty implementation of the abstract submit class.
  }


  /**
   * Defines the settings form for Unconverted links entities.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Form definition array.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['UnconvertedLinks_settings']['#markup'] = 'Settings form for Unconverted links entities. Manage field settings here.';
    return $form;
  }

}
