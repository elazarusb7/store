<?php
/**
 * @file
 * Contains \Drupal\samhsa_xml\Form\SamhsaOrdersXmlFormController
 */

namespace Drupal\samhsa_xml\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\samhsa_xml\API\SamhsaXmlAPI;


class ProcessFulfilledOrdersFormController extends FormBase
{

  public function getFormId() {
    return 'samhsa_xml_process_fulfilled_orders';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
      $validators = array(
        'file_validate_extensions' => array('csv json xml'),
      );
      $form['info'] = array(
        '#type' => 'markup',
        '#markup' => '<p style="text-align:right">' . t('This form processes a file of order number that have been fulfilled by GPO and sets their status to "completed"') . '</p>',
      );
      $form['upload'] = array(
        '#type' => 'managed_file',
        '#size' => 20,
        '#title' => t('Upload file of fulfilled orders.'),
        '#description' => t('CSV, JSON or XML'),
        '#upload_validators' => $validators,
        '#upload_location' => 'public://fulfilled_orders/',
        '#required' => true,
      );
      $form['actions']['#type'] = 'actions';
      $form['actions']['submit'] = array(
        '#type' => 'submit',
        '#value' => $this->t('Upload and process'),
        '#button_type' => 'primary',
      );

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('upload') == NULL) {
      $form_state->setErrorByName('upload', $this->t('File.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_file = $form_state->getValue('upload', 0);
    if (isset($form_file[0]) && !empty($form_file[0])) {
      $file = File::load($form_file[0]);
      SamhsaXmlAPI::processFulfilledOrder($file);
    }
  }
}
