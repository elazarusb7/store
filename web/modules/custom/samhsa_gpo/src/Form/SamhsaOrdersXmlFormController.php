<?php
/**
 * @file
 * Contains \Drupal\samhsa_gpo\Form\SamhsaOrdersXmlFormController
 */

namespace Drupal\samhsa_gpo\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\samhsa_gpo\API\SamhsaGpoAPI;


class SamhsaOrdersXmlFormController extends FormBase
{

  public function getFormId() {
    return 'samhsa_gpo_orders_generate_xml';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['info'] = array(
      '#type' => 'markup',
      '#markup' => '<p style="text-align:right">' . t("This form generates an XML export of a single day's orders. It will only perform exports from 8-23-2022 to yesterday. It will not output any date prior to 8-23-2022 because that's the day GPO took over fullfillment. It will not output today's orders because it's intended to generate an entire days orders.") . '</p>',
    );
    $form['date'] = array(
      '#type' => 'textfield',
      '#title' => t('Date (YYYY-MM-DD)'),
      '#default_value' => date("Y-m-d", strtotime("yesterday")),
      '#required' => true,
    );
    $form['special_requests'] = array(
      '#type' => 'checkbox',
      '#title' => 'Special Requests'
    );
    $form['product_type'] = array(
      '#type' => 'select',
      '#title' => 'Order Type',
      '#options' => [
        'all' => 'All',
        'non-988' => 'Non 988 only',
        '988' => '988 Only'
      ],
      '#default_value' => 'all',
      '#description' => t('If "Special Requests" is selected the Order type setting will be ignored. "All" will always be returned.')
    );
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Generate XML'),
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $cutoffDate = '2022-08-23';
    $submittedDateTS = strtotime($form_state->getValue('date'));
    if ($submittedDateTS < strtotime($cutoffDate)) {
      $form_state->setErrorByName('date', $this->t('Dates prior to @cutoffDate cannot be exported.', ['@cutoffDate' => $cutoffDate]));
    }
    $exportExists = SamhsaGpoAPI::testForExport($form_state->getValue('date'), $form_state->getValue('special_requests'), $form_state->getValue('product_type'));
    if ($exportExists) {
      $form_state->setErrorByName('date', $this->t('An export for this date already exists.'));
    }
    $todayTS = strtotime(date('Y-m-d', time()));
    if ($submittedDateTS >= $todayTS) {
      $form_state->setErrorByName('date', $this->t('You can\'t export orders from today on. Only from yesterday or before are allowed.', ['@cutoffDate' => $cutoffDate]));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $date = $form_state->getValue('date');
    $special_requests = $form_state->getValue('special_requests');
    if ($special_requests == 1) {
      $product_type = 'all';
    }
    else {
      $product_type = $form_state->getValue('product_type');
    }

    SamhsaGpoAPI::generateXML($date, $special_requests, $product_type);
  }
}
