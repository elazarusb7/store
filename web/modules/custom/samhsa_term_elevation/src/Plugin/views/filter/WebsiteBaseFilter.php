<?php

namespace Drupal\samhsa_term_elevation\Plugin\views\filter;

use Drupal\views\ManyToOneHelper;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\ManyToOne;
use Drupal\views\ViewExecutable;

/**
 * Filters by phase or status of project.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("website_base_filter")
 */
class WebsiteBaseFilter extends ManyToOne {

  /**
   * Websites that compound the multisite environment.
   *
   * @var array
   *   List of all websites that compound the multisite environment.
   */
  protected $websitesBases = [];

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->valueTitle = t('Filter by website:');
    $this->definition['options callback'] = [$this, 'generateOptions'];
  }

  /**
   * Helper function that generates the options.
   *
   * @return array
   *   An array of states and their ids.
   */
  public function generateOptions() {
    $this->websitesBases += \Drupal::service('samhsa_te_solr_connections')->getWebsitesBases();
    $sites_list = unserialize(\Drupal::config('samhsa_term_elevation.websites_aliases')->get('sites'));
    foreach ($this->websitesBases as &$website) {
      if (@$sites_list[$website]) {
        $website = $sites_list[$website];
      }
    }
    return $this->websitesBases;
  }

  /**
   * Helper function that builds the query.
   */
  public function query() {
    // This should be kept declared, since it's implementation is required.
    // However, adding a "where" to the query object didn't affect the
    // query sent to Solr. The implementation was done in
    // samhsa_term_elevation_search_api_solr_query_alter().
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['operator']['default'] = 'or';
    $options['value']['default'] = ['All'];

    if (isset($this->helper)) {
      $this->helper->defineOptions($options);
    }
    else {
      $helper = new ManyToOneHelper($this);
      $helper->defineOptions($options);
    }

    return $options;
  }

}
