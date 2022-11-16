<?php
/**
 * @file
 * Contains \Drupal\samhsa_xml\Form\SamhsaOrdersXmlFormController
 */

namespace Drupal\samhsa_xml\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\samhsa_xml\API\SamhsaXmlAPI;


class SamhsaOrdersXmlFormController extends FormBase
{

  public function getFormId() {
    return 'samhsa_xml_orders_generate_xml';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['info'] = array(
      '#type' => 'markup',
      '#markup' => '<p style="text-align:right">' . t("Description.") . '</p>',
    );
    $form['date'] = array(
      '#type' => 'textfield',
      '#title' => t('Date (YYYY-MM-DD)'),
      '#default_value' => date("Y-m-d", strtotime("yesterday")),
      '#required' => true,
    );
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Generate XML'),
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $cutoffDate = '2022-11-01';
    $submittedDateTS = strtotime($form_state->getValue('date'));
    if ($submittedDateTS < strtotime($cutoffDate)) {
      $form_state->setErrorByName('date', $this->t('Dates prior to @cutoffDate cannot be exported.', ['@cutoffDate' => $cutoffDate]));
    }
    $exportExists = SamhsaXmlAPI::testForExport($form_state->getValue('date'));
    if ($exportExists) {
      $form_state->setErrorByName('date', $this->t('An export for this date already exists.'));
    }
    $todayTS = strtotime(date('Y-m-d', time()));
    if ($submittedDateTS >= $todayTS) {
      $form_state->setErrorByName('date', $this->t('You can\'t export orders from today on. Only from yesterday or before are allowed.', ['@cutoffDate' => $cutoffDate]));
    }
  }

  //https://stackoverflow.com/questions/486757/how-to-generate-xml-file-dynamically-using-php
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $date = $form_state->getValue('date');
    $addend = $form_state->getValue('addend');
    SamhsaXmlAPI::generateXML($date);
  }
}
