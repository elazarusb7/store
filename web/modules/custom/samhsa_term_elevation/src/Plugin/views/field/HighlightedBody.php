<?php

namespace Drupal\samhsa_term_elevation\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Random;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("highlighted_body")
 */
class HighlightedBody extends FieldPluginBase {

  private $highlightFieldName = '';

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $config = \Drupal::config('samhsa_term_elevation.config');
    $this->highlightFieldName = $config->get('highlight_id');
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

    if (!($values->_object instanceof \Drupal\search_api_solr\Plugin\DataType\SolrMultisiteDocument)) {
      $result = NULL;
    }
    elseif ($highlight = $values->_item->getExtraData('highlight')) {
      $highlighted_snippets = NULL;
      foreach ($highlight[$this->highlightFieldName] as $item) {
        $item = strip_tags($item, '<em>');
        $highlighted_snippets .= "<span class='te-highlighted'>$item</span>";
      }
      $result = [
        '#type' => 'markup',
        '#markup' => "<div class='te-highlighted-wrapper'>...$highlighted_snippets...</div>"
      ];
    }
    else {
      $result = NULL;
    }

    return $result;
  }

}
