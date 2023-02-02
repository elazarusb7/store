<?php

namespace Drupal\migrate_files_and_images\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class MigrateFilesAndImagesForm.
 *
 * @package Drupal\migrate_files_and_images\Form
 */
class MigrateFilesAndImagesForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'migrate_files_and_images_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['files_limit_number'] = [
      '#type' => 'textfield',
      '#description' => $this->t('Leave zero to migrate all files.'),
      '#title' => $this->t('Maximum number of files to be migrated'),
      '#size' => 6,
      '#default_value' => 0,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#title' => $this->t('Migrating Files and Images'),
      '#value' => $this->t('Execute migration of files and images'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('migrate_files_and_images.import_files_and_images_execute_import',
      ['limit' => $form_state->getValue('files_limit_number')]);
  }

}
