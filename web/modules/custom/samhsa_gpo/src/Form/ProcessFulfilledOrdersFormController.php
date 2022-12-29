<?php
/**
 * @file
 * Contains \Drupal\samhsa_gpo\Form\SamhsaOrdersXmlFormController
 */

namespace Drupal\samhsa_gpo\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\samhsa_gpo\API\SamhsaGpoAPI;


class ProcessFulfilledOrdersFormController extends FormBase
{

  public function getFormId() {
    return 'samhsa_gpo_process_fulfilled_orders';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
      $validators = array(
        'file_validate_extensions' => array('csv json xml'),
      );
      $form['info'] = array(
        '#type' => 'markup',
        '#markup' => '<p style="text-align:right">' . t('This form processes a file of GPO order numbers that have been fulfilled by GPO and sets the matching Drupal Commerce Order status to "completed"') . '</p>',
      );
      $form['upload'] = array(
        '#type' => 'managed_file',
        '#size' => 20,
        '#title' => t('Upload CSV file of fulfilled order numbers.'),
        '#upload_validators' => $validators,
        '#upload_location' => 'public://fulfilled_orders/',
        '#required' => true,
      );
      $form['actions']['#type'] = 'actions';
      $form['actions']['submit'] = array(
        '#type' => 'submit',
        '#value' => $this->t('Process these orders'),
        '#description' => $this->t('This CANNOT BE UNDONE!')
      );
      $form['actions']['warning'] = [
        '#type' => 'markup',
        '#markup' => '<p>' . $this->t('This CANNOT BE UNDONE!') . '</p>',
      ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('upload') == NULL) {
      $form_state->setErrorByName('upload', $this->t('File.'));
    }
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return void
   *
   * Note that we are not setting the uploaded file to permanent. This means the file will be purged by CRON 6 hours later.
   * Which is fine. we don't need this file to hang around. Once it's been processed it has no further use.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_file = $form_state->getValue('upload', 0);
    if (isset($form_file[0]) && !empty($form_file[0])) {
      $file = File::load($form_file[0]);
      SamhsaGpoAPI::processFulfilledOrder($file);
    }
  }
}
