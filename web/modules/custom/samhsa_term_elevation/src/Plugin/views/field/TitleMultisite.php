<?php

namespace Drupal\samhsa_term_elevation\Plugin\views\field;

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
 * @ViewsField("title_multisite")
 */
class TitleMultisite extends FieldPluginBase {

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
    $form['link_to_page'] = [
      '#type' => 'checkbox',
      '#title' => 'Link to the node\'s page',
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

    $result = $fields[$this->titleFieldId][0];

    if ($this->options['link_to_page']) {

      if (substr(@$fields['site'], -1) == '/') {
        $fields['site'] = substr($fields['site'], 0, -1);
      }

      if ($this->friendlyUrlFieldId && isset($fields[$this->friendlyUrlFieldId])) {
        $content_url_pieces = explode('/', $fields[$this->friendlyUrlFieldId]);
        $content_url_pieces = array_filter($content_url_pieces);
        $content_url = implode('/', $content_url_pieces);
        $string = $fields['site'] . '/' . $content_url;
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
      $result = Link::fromTextAndUrl($fields[$this->titleFieldId][0], $url)->toString();

    }

    return $result;

  }

}
