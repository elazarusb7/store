<?php

/**
 * @file
 * Contains \Drupal\migrate_files_and_images\Form\MigrateFilesAndImagesForm.
 */

namespace Drupal\migrate_files_and_images\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class MigrateFilesAndImagesSettingsForm.
 *
 * @package Drupal\migrate_files_and_images\Form
 */
class MigrateFilesAndImagesSettingsForm extends ConfigFormBase {

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
    $image_path = $this->config('migrate_files_and_images.settings')
        ->get('images_path');

    $form['images_path'] = array(
      '#type' => 'textfield',
      '#description' => $this->t('Path, where source images files are stored.'),
      '#title' => $this->t('Path to source images location'),
      '#size' => 60,
      '#default_value' => isset($image_path) ? $image_path : '/sites/default/files/d7/',
    );
    $documents_path = $this->config('migrate_files_and_images.settings')
        ->get('documents_path');
    $form['documents_path'] = array(
      '#type' => 'textfield',
      '#description' => $this->t('Path, where source documents files are stored.'),
      '#title' => $this->t('Path to source documents location'),
      '#size' => 60,
      '#default_value' => isset($documents_path) ? $documents_path : '/sites/default/files/d7/priv/',
    );

    return parent::buildForm($form, $form_state);
  }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $values = $form_state->getValues();

        $this->config('migrate_files_and_images.settings')
            ->set('images_path', $values['images_path'])
            ->save();
        $this->config('migrate_files_and_images.settings')
            ->set('documents_path', $values['documents_path'])
            ->save();

        parent::submitForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames() {
        return ['migrate_files_and_images.settings'];
    }


}
