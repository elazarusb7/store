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
//      '#default_value' => date("Y-m-d", strtotime("yesterday")),
      '#default_value' => '2022-05-15',
      '#required' => true,
    );
    $form['addend'] = array(
      '#type' => 'textfield',
      '#title' => t('Addend (Integer to be added to the Order ID'),
      '#required' => true,
      '#default_value' => '8037808'
    );
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Generate XML'),
    ];

    return $form;
  }

  //https://stackoverflow.com/questions/486757/how-to-generate-xml-file-dynamically-using-php
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $date = $form_state->getValue('date');
    $addend = $form_state->getValue('addend');
    SamhsaXmlAPI::generateXML($date, $addend);
  }
}
