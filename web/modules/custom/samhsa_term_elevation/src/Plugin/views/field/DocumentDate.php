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
 * @ViewsField("document_date")
 */
class DocumentDate extends FieldPluginBase {

  /**
   * Stores the machine name of the Document Date field.
   *
   * @var string
   */
  private $documentDateFieldName = '';

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $config = \Drupal::config('samhsa_term_elevation.config');
    $this->documentDateFieldName = $config->get('document_date_id');
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
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['hide_alter_empty'] = ['default' => FALSE];
    $options['document_date_format'] = ['default' => 0];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $form['document_date_format'] = [
      '#type' => 'radios',
      '#title' => 'Document Date Format',
      '#options' => [
        0 => $this->t('Date and time'),
        1 => $this->t('Date only'),
      ],
      '#default_value' => $this->options['document_date_format'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {

    if (!($values->_object instanceof SolrMultisiteDocument)) {
      return 'n/a';
    }

    $fields = $values->_item->getExtraData('search_api_solr_document')->getFields();

    if (isset($fields[$this->documentDateFieldName])) {
      $option = $this->options['document_date_format'];
      if ($option == 0) {
        $result = date('m-d-Y h:i:s', $fields[$this->documentDateFieldName][0]);
      }
      else {
        $result = date('m-d-Y', $fields[$this->documentDateFieldName][0]);
      }
    }
    else {
      $result = NULL;
    }

    return $result;
  }

}
