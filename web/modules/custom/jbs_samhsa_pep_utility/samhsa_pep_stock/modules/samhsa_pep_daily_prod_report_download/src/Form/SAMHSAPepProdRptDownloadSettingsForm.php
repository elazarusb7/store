<?php

namespace Drupal\samhsa_pep_daily_prod_report_download\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class SAMHSAPepProdRptDownloadSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'samhsa_pep_prod_rpt_download_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $hour = $this->config('samhsa_pep_daily_prod_report_download.settings')
      ->get('samhsa_daily_prod_report_download_hour');
    $form['samhsa_daily_prod_report_download_hour'] = [
      '#type' => 'number',
      '#title' => t('Hour'),
      '#default_value' => $hour,
      '#size' => 4,
      '#max' => 24,
      '#min' => 0,
      '#maxlength' => 2,
      '#description' => t("Hour when report should run."),
      '#required' => TRUE,
    ];

    $path_to_save = $this->config('samhsa_pep_daily_prod_report_download.settings')
      ->get('path_to_save');
    $form['path_to_save'] = [
      '#type' => 'textfield',
      '#title' => t('Report Download Directory'),
      '#default_value' => $path_to_save ?? 'public://publication_transactions_rpt/',
      '#size' => 60,
      // '#maxlength' => 15,
      '#description' => t("Directory where report should be downloaded at."),
      '#required' => FALSE,
    ];

    $file_name_prefix = $this->config('samhsa_pep_daily_prod_report_download.settings')
      ->get('file_name_prefix');
    $form['file_name_prefix'] = [
      '#type' => 'textfield',
      '#title' => t('Report File Name Prefix'),
      '#default_value' => $file_name_prefix ?? 'Product-Status-Report-',
      '#size' => 60,
      '#description' => t("Prefix for the report"),
      '#required' => FALSE,
    ];

    $last_pub_trans_rpt_date = $this->config('samhsa_pep_daily_prod_report_download.settings')
      ->get('last_pub_trans_rpt_date');
    $timeStamp = isset($last_pub_trans_rpt_date) ? date("m/d/Y H:s", $last_pub_trans_rpt_date) : 'Never';
    $form['last_pub_trans_rpt_date'] = [
      '#type' => 'textfield',
      '#title' => t('Date Last Ran: ' . $timeStamp),
      '#default_value' => $last_pub_trans_rpt_date,
      '#size' => 15,
      '#maxlength' => 15,
      '#description' => t("Date Last time download ran."),
      '#required' => FALSE,
      // '#disabled' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('samhsa_pep_daily_prod_report_download.settings')
      ->set('samhsa_daily_prod_report_download_hour', $values['samhsa_daily_prod_report_download_hour'] ?? 'oz')
      ->set('last_pub_trans_rpt_date', $values['last_pub_trans_rpt_date'])
      ->set('path_to_save', $values['path_to_save'])
      ->set('file_name_prefix', $values['file_name_prefix'])
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['samhsa_pep_daily_prod_report_download.settings'];
  }

}
