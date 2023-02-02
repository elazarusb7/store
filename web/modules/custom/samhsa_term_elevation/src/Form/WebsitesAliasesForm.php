<?php

namespace Drupal\samhsa_term_elevation\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for the Website Base Aliases.
 */
class WebsitesAliasesForm extends ConfigFormBase {

  /**
   * List of the sites.
   *
   * @var array
   */
  private $sites = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    parent::__construct($config_factory);
    $te_config = $this->config('samhsa_term_elevation.config');
    $server_id = $te_config->get('server_id');
    $this->sites = \Drupal::service('samhsa_te_solr_connections')->getMultiSiteComponents($server_id);
    $a = 1;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'samhsa_term_elevation.websites_aliases',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'websites_aliases_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $sites_list = unserialize($this->config('samhsa_term_elevation.websites_aliases')->get('sites'));

    $form['description'] = [
      '#type' => 'item',
      '#title' => $this->t('Sites that compound the multi-site environment'),
      '#description' => $this->t("Provide the aliases for the Websites' base URLs"),
    ];

    foreach ($this->sites as $key => $site) {
      $form['site_' . $key] = [
        '#type' => 'textfield',
        '#title' => $site,
        '#default_value' => $sites_list[$site],
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $sites_list = [];
    foreach ($this->sites as $key => $site) {
      $field_name = 'site_' . $key;
      $sites_list[$site] = $form_state->getValue($field_name);
    }
    $this->config('samhsa_term_elevation.websites_aliases')
      ->set('sites', serialize($sites_list))
      ->save();
  }

}
