<?php

/**
 * @file
 * Contains Drupal\samhsa_pep_pdf_printing\Form\PepPdfConfig.
 */

namespace Drupal\samhsa_pep_pdf_printing\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class PepPdfConfig.
 *
 * @package Drupal\samhsa_pep_pdf_printing\Form
 */
class SamhsaPepPdfConfig extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'samhsa_pep_pdf_printing.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pep_pdf_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $left_margin_col_1 = 10;
    $left_margin_col_2 = 100;
    $top_margin = 5;
    $top_label_delta = 50;
    $line_step = 5;
    $rows_per_page = 3;
    $font_name = 'Arial';
    $font_style = 'B';
    $font_size = 12;

    $config = $this->config('samhsa_pep_pdf_printing.config');


    $form['layout'] = array(
      '#type' => 'fieldset',
      '#title' => t('Layout'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#weight' => 10,
    );
    $form['layout']['left_margin_col_1'] = [
      '#type' => 'number',
      '#title' => $this->t('Left margin of the left column'),
      '#description' => $this->t('Defines the left margin of the first column in millimeters.'),
      '#default_value' => $config->get('left_margin_col_1'),
      '#step' => 0.1,
      '#weight' => 15,
    ];
    $form['layout']['left_margin_col_2'] = [
      '#type' => 'number',
      '#title' => $this->t('Left margin of the right column'),
      '#description' => $this->t('Defines the left margin of the second column in millimeters.'),
      '#default_value' => $config->get('left_margin_col_2'),
      '#step' => 0.1,
      '#weight' => 20,
    ];
    $form['layout']['top_margin'] = [
      '#type' => 'number',
      '#title' => $this->t('Top margin'),
      '#description' => $this->t('Defines the margin of the first row of labels in millimeters.'),
      '#default_value' => $config->get('top_margin'),
      '#step' => 0.1,
      '#weight' => 25,
    ];
    $form['layout']['label_height'] = [
      '#type' => 'number',
      '#title' => $this->t('Label height'),
      '#description' => $this->t('Defines the height of the labels in millimeters.'),
      '#default_value' => $config->get('label_height'),
      '#step' => 0.1,
      '#weight' => 30,
    ];
    $form['layout']['label_width'] = [
      '#type' => 'number',
      '#title' => $this->t('Label width'),
      '#description' => $this->t('Defines the width of the labels in millimeters.'),
      '#default_value' => $config->get('label_width'),
      '#step' => 0.1,
      '#weight' => 30,
    ];
    $form['layout']['line_height'] = [
      '#type' => 'number',
      '#title' => $this->t('Line height'),
      '#description' => $this->t('Defines the line height in millimeters.'),
      '#default_value' => $config->get('line_height'),
      '#step' => 0.1,
      '#weight' => 35,
    ];
    $form['layout']['rows_per_page'] = [
      '#type' => 'number',
      '#title' => $this->t('Rows per page'),
      '#description' => $this->t('Defines the number of rows on each page.'),
      '#default_value' => $config->get('rows_per_page'),
      '#min' => 1,
      '#weight' => 40,
    ];

    $form['font'] = array(
      '#type' => 'fieldset',
      '#title' => t('Font'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#weight' => 20,
    );
    $form['font']['font_name'] = [
      '#type' => 'select',
      '#title' => $this->t('Name'),
      '#options' => [
        'Courier' => $this->t('Courier (fixed-width)'),
        'Arial' => $this->t('Helvetica or Arial (synonymous; sans serif)'),
        'Times' => $this->t('Times (serif)'),
      ],
      '#default_value' => $config->get('font_name'),
      '#weight' => 15,
    ];
    $form['font']['font_style'] = [
      '#type' => 'select',
      '#title' => $this->t('Style'),
      '#options' => [
        NULL => $this->t('Normal'),
        'B' => $this->t('Bold'),
        'I' => $this->t('Italic'),
        'U' => $this->t('Underline'),
      ],
      '#default_value' => $config->get('font_style'),
      '#weight' => 20,
    ];
    $form['font']['font_size'] = [
      '#type' => 'number',
      '#title' => $this->t('Size'),
      '#default_value' => $config->get('font_size'),
      '#min' => 8,
      '#max' => 30,
      '#weight' => 25,
    ];

    /*$form['test'] = array(
      '#type' => 'fieldset',
      '#title' => t('Generate testing labels'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#weight' => 30,
    );
    $form['test']['number_of_test_rows'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of rows to be printed'),
      '#default_value' => $config->get('number_of_test_rows'),
      '#min' => 1,
      '#max' => 50,
      '#weight' => 10,
    ];
    $form['test']['test_button'] = [
      '#type' => 'submit',
      '#name' => 'test_button',
      '#value' => $this->t('Generate'),
      '#weight' => 15,
    ];*/

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('samhsa_pep_pdf_printing.config')
      ->set('left_margin_col_1', $form_state->getValue('left_margin_col_1'))
      ->set('left_margin_col_2', $form_state->getValue('left_margin_col_2'))
      ->set('top_margin', $form_state->getValue('top_margin'))
      ->set('label_height', $form_state->getValue('label_height'))
      ->set('label_width', $form_state->getValue('label_width'))
      ->set('line_height', $form_state->getValue('line_height'))
      ->set('rows_per_page', $form_state->getValue('rows_per_page'))
      ->set('font_name', $form_state->getValue('font_name'))
      ->set('font_style', $form_state->getValue('font_style'))
      ->set('font_size', $form_state->getValue('font_size'))
      ->set('number_of_test_rows', $form_state->getValue('number_of_test_rows'))

      ->save();
    /*$triggering_element = $form_state->getTriggeringElement();
    if ($triggering_element['#name'] == 'test_button') {
      \Drupal::service('samhsa_pep_pdf_printing.label')->generateTestLabels($form_state->getValue('number_of_test_rows'));
    }*/
  }

}
