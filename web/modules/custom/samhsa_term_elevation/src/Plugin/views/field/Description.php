<?php

namespace Drupal\samhsa_term_elevation\Plugin\views\field;

use Drupal\search_api_solr\Plugin\DataType\SolrMultisiteDocument;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("description")
 */
class Description extends FieldPluginBase {
  /**
   * Stores the machine name of the Document Date field.
   *
   * @var string
   */
  private $descriptionFieldName = '';

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $config = \Drupal::config('samhsa_term_elevation.config');
    $this->descriptionFieldName = $config->get('description_id');
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
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {

    if (!($values->_object instanceof SolrMultisiteDocument)) {
      return 'n/a';
    }

    $fields = $values->_item->getExtraData('search_api_solr_document')->getFields();

    if (isset($fields[$this->descriptionFieldName])) {
      $result = $fields[$this->descriptionFieldName][0];
    }
    else {
      $result = NULL;
    }

    return $result;
  }

}
