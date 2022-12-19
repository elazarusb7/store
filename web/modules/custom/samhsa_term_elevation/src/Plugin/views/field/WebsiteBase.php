<?php

namespace Drupal\samhsa_term_elevation\Plugin\views\field;

use Drupal;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\search_api_solr\Plugin\DataType\SolrMultisiteDocument;

/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("website_base")
 */
class WebsiteBase extends FieldPluginBase {

  /**
   * Aliases.
   *
   * @var array
   *   List of aliases of the websites that compound the multisite environment.
   */
  private $sitesAliases = [];

  /**
   * URL replacements.
   *
   * @var array
   *   List of URL replacements of the websites that compound the multisite
   *   environment.
   */
  private $urlReplacements = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $sites_aliases = unserialize(Drupal::config('samhsa_term_elevation.websites_aliases')
      ->get('sites'));
    $this->urlReplacements = unserialize(Drupal::config('samhsa_te_url_replacement.configuration')
      ->get('sites'));
    $this->sitesAliases = [];
    foreach ($sites_aliases as $key => $value) {
      if ($this->urlReplacements[$key]) {
        $this->sitesAliases[$this->urlReplacements[$key]] = $value;
      }
      else {
        $this->sitesAliases[$key] = $value;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Do nothing -- to override the parent query.
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['hide_alter_empty'] = ['default' => FALSE];
    $options['link_to_site'] = ['default' => FALSE];
    $options['use_aliases'] = ['default' => FALSE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $form['link_to_site'] = [
      '#type' => 'checkbox',
      '#title' => 'Link to site',
      '#default_value' => $this->options['link_to_site'],
    ];
    $form['use_aliases'] = [
      '#type' => 'checkbox',
      '#title' => 'Use aliases',
      '#default_value' => $this->options['use_aliases'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {

    if (!($values->_object instanceof SolrMultisiteDocument)) {
      return 'n/a';
    }

    $fields = $values->_item->getExtraData('search_api_solr_document')
      ->getFields();

    if ($this->options['use_aliases'] && $this->sitesAliases[$fields['site']]) {
      $site_name = $this->sitesAliases[$fields['site']];
    }
    else {
      $site_name = $fields['site'];
    }

    if (isset($fields['site'])) {
      if ($this->options['link_to_site']) {
        $url = Url::fromUri($fields['site']);
        $result = Link::fromTextAndUrl($site_name, $url)->toString();
      }
      else {
        $result = $site_name;
      }
    }
    else {
      $result = NULL;
    }

    return $result;

  }

}
