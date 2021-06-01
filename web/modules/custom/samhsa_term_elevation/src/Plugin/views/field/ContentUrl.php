<?php

namespace Drupal\samhsa_term_elevation\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\search_api_solr\Plugin\DataType\SolrMultisiteDocument;

/**
 * Field Content URL.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("content_url")
 */
class ContentUrl extends FieldPluginBase {

  /**
   * Identifier of the title field.
   *
   * @var string
   */
  private $titleFieldId = NULL;

  /**
   * Identifier of the friendly URLs field.
   *
   * @var string
   */
  private $friendlyUrlFieldId = NULL;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $config = \Drupal::config('samhsa_term_elevation.config');
    $this->titleFieldId = $config->get('title_id');
    $this->friendlyUrlFieldId = $config->get('url_id');
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
    $options['exclude_protocol'] = ['default' => FALSE];
    $options['hide_alter_empty'] = ['default' => FALSE];
    $options['link_to_page'] = ['default' => FALSE];
    $options['generate_class_wrapper'] = ['default' => FALSE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $form['exclude_protocol'] = [
      '#type' => 'checkbox',
      '#title' => 'Exclude "http://" and "https://" from  link\'s text',
      '#default_value' => $this->options['exclude_protocol'],
    ];
    $form['link_to_page'] = [
      '#type' => 'checkbox',
      '#title' => "Link to the node's page",
      '#default_value' => $this->options['link_to_page'],
    ];
    $form['generate_class_wrapper'] = [
      '#type' => 'checkbox',
      '#title' => 'Class wrapper',
      '#description' => 'Generates a class wrapper indicating the node is elevated.',
      '#default_value' => $this->options['generate_class_wrapper'],
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

    if (substr(@$fields['site'], -1) == '/') {
      $fields['site'] = substr($fields['site'], 0, -1);
    }
    $content_url_pieces = explode('/', $fields[$this->friendlyUrlFieldId]);
    $content_url_pieces = array_filter($content_url_pieces);
    $content_url = implode('/', $content_url_pieces);
    $result = $fields['site'] . '/' . $content_url;

    if ($this->options['link_to_page']) {

      if ($this->friendlyUrlFieldId && isset($fields[$this->friendlyUrlFieldId])) {
        $string = $result;
      }
      else {
        preg_match('/node\/(.*?):/', $fields['id'], $matches);
        if (isset($matches[1])) {
          $string = "{$fields['site']}/node/{$matches[1]}";
        }
      }

      $link_options = [];
      if ($this->options['generate_class_wrapper']) {
        $flag_elevated_item = $values->_item->getExtraData('elevated_item');
        if ($flag_elevated_item) {
          $link_options = [
            'attributes' => [
              'class' => [
                'elevated-term',
              ],
            ],
          ];
        }
      }
      $url = Url::fromUri($string, $link_options);
      if ($this->options['exclude_protocol']) {
        $result = str_replace(['http://', 'https://'], NULL, $result);
      }
      $result = Link::fromTextAndUrl($result, $url)->toString();

    }

    return $result;

  }

}
