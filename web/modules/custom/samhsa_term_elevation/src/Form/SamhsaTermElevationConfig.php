<?php

namespace Drupal\samhsa_term_elevation\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class SamhsaTermElevationConfig.
 */
class SamhsaTermElevationConfig extends ConfigFormBase {

  private const DELIMITTER = '_#_';

  /**
   * List of Views fields plugins to be mapped.
   *
   * @var array
   *
   * @todo get the same id as the plugins.
   */
  protected $pluginIds = [];

  /**
   * List of mapped Views fields plugins.
   *
   * @var array
   */
  protected $pluginsMap = [];

  /**
   * Map of the checkboxes states.
   *
   * @var array
   */
  protected $checkboxesMap = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    parent::__construct($config_factory);
    $this->pluginIds = _samhsa_term_elevation_get_plugins_map();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'samhsa_term_elevation.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'samhsa_term_elevation';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('samhsa_term_elevation.config');
    $default_server_id = $config->get('server_id');
    $default_index_id = $config->get('index_id');

    foreach ($this->pluginIds as $plugin_id => $plugin_name) {
      $this->pluginsMap[$plugin_id] = $config->get($plugin_id);
    }

    $form['general'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('General Settings'),
      '#wrapper_attributes' => ['class' => 'container'],
    ];
    $form['general']['use_elevation'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use elevation'),
      '#description' => $this->t('Check this if elevation of terms should be used on searches.'),
      '#default_value' => $config->get('use_elevation'),
    ];
    $form['general']['force_elevation'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Force elevation'),
      '#description' => $this->t('Check this if elevation of terms should be forced over sort.'),
      '#default_value' => $config->get('force_elevation'),
    ];

    $form['servers_fields'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Servers'),
      '#wrapper_attributes' => ['class' => 'container'],
    ];

    $weight = 0;
    $servers = [];
    foreach (search_api_solr_get_servers() as $server) {

      $server_id = $server->id();
      $server_name = $server->get('name');
      $servers[$server_id] = $server_name;

      $form['servers_fields'][$server_id] = [
        '#type' => 'fieldset',
        '#title' => $server_name,
        '#weight' => $weight += 10,
      ];
      $form['servers_fields'][$server_id] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Indexes and fields in %server', ['%server' => $server_name]),
        '#states' => [
          'visible' => [
            ':input[name="server"]' => ['value' => $server_id],
          ],
        ],
      ];

      $indexes = [];
      foreach ($server->getIndexes() as $index) {
        $indexes[$index->id()] = $index->get('name');
      }
      $form['servers_fields'][$server_id]['index_' . $server_id] = [
        '#type' => 'radios',
        '#title' => $this->t('Indexes'),
        '#options' => $indexes,
        '#default_value' => isset($indexes[$default_index_id]) ? $default_index_id : NULL,
      ];

      $header = [$this->t('Solr index fields')] + $this->pluginIds;

      $field_names = \Drupal::service('samhsa_te_solr_connections')->getIndexedFieldNames($server_id);
      $field_names = array_combine($field_names, $field_names);

      $data = [];
      foreach ($field_names as $field_id) {
        $field_id = str_replace(["\r\n", "\n", "\r"], NULL, $field_id);
        $columns = [$field_id];
        foreach ($this->pluginIds as $plugin_id => $void) {
          $columns[] = [
            'data' => $this->buildCheckboxElement($server_id, $plugin_id, $field_id),
          ];
        }
        $data[] = $columns;
      }

      $form['servers_fields'][$server_id]['table_' . $server_id] = [
        '#theme' => 'table',
        '#caption' => $this->t('Map the plugins to the fields'),
        '#header' => $header,
        '#rows' => $data,
        '#attributes' => [
          'class' => [
            'te-radios-grid-table',
          ],
        ],
      ];

    }

    $form['servers_fields']['server'] = [
      '#type' => 'radios',
      '#title' => $this->t('Servers to be used used on searches with Term Elevation functionality'),
      '#options' => $servers,
      '#default_value' => $default_server_id ? $default_server_id : NULL,
      '#weight' => -100,
    ];

    $form['#attached']['library'][] = 'samhsa_term_elevation/managing_radios';
    $form['#attached']['drupalSettings']['teRadiosManagement']['checkboxesMap'] = $this->checkboxesMap;
    $form['#tree'] = FALSE;

    $form['connection_test'] = [
      '#type' => 'details',
      '#title' => $this->t('Connection test'),
      '#open' => FALSE,
    ];
    $form['connection_test']['search_term'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search term'),
    ];
    $form['connection_test']['search_button'] = [
      '#type' => 'button',
      '#value' => $this->t('Search'),
      '#ajax' => [
        'callback' => '::performSearch',
        'method' => 'replace',
        'disable-refocus' => TRUE,
      ],
    ];
    $form['connection_test']['search_response'] = [
      '#type' => 'markup',
      '#markup' => '<div id="te-search-results">' . $this->t('Response object will be displayed here.') . '</div>',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Build the render array of the checkbox element.
   *
   * @param string $server_id
   *   Solr Server id.
   * @param string $plugin_id
   *   Plugin id.
   * @param string $field_id
   *   Field id.
   *
   * @return array
   *   Render array of the checkbox element.
   */
  private function buildCheckboxElement($server_id, $plugin_id, $field_id) {
    $name = $plugin_id . self::DELIMITTER . $field_id;
    if ($this->pluginsMap[$plugin_id] == $field_id) {
      $this->checkboxesMap[] = $name;
    }
    $element = [
      '#id' => $plugin_id . '__' . $field_id,
      '#type' => 'checkbox',
      '#title' => NULL,
      '#name' => $name,
      '#default_value' => FALSE,
      '#attributes' => [
        'vertical_group' => [$plugin_id],
        'horizontal_group' => [$field_id],
        'plugin_id' => [$plugin_id],
        'field_id' => [$field_id],
      ],
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    parent::submitForm($form, $form_state);

    $values = $form_state->getValues();

    $radio_values = [];
    $inputted_values = $form_state->getUserInput();
    foreach ($inputted_values as $inputted_field => $void) {
      $inputted_field = str_replace(["\r\n", "\n", "\r"], ' ', $inputted_field);
      if (preg_match('/' . self::DELIMITTER . '/', $inputted_field)) {
        $field_config = explode(self::DELIMITTER, $inputted_field);
        $radio_values[$field_config[0]] = $field_config[1];
      }
    }

    $config = $this->config('samhsa_term_elevation.config');
    $config->set('use_elevation', $form_state->getValue('use_elevation'))
      ->set('force_elevation', $form_state->getValue('force_elevation'))
      ->set('server_id', $values['server'])
      ->set('index_id', $values['index_' . $values['server']]);

    foreach (array_keys($this->pluginIds) as $plugin_id) {
      if ($radio_values[$plugin_id]) {
        $config->set($plugin_id, $radio_values[$plugin_id]);
      }
      else {
        $config->clear($plugin_id);
      }
    }

    $config->save();

  }

  /**
   * Callback for AJAX.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form State object=.
   *
   * @todo Include elevation in the results.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   AjaxResponse object.
   */
  public function performSearch(array &$form, FormStateInterface &$form_state) {
    $response = new AjaxResponse();
    $term = $form_state->getValue('connection_test')['search_term'];
    $results = \Drupal::service('samhsa_te_solr_connections')->searchString($term);
    $results_json = new JsonResponse($results);
    $content_json = $results_json->getContent();
    $content_json = $this->jsonPrettyPrint($content_json);
    $content_json = str_replace("\n", '<br>', $content_json);
    $response->addCommand(new InvokeCommand('#te-search-results', 'html', [$content_json]));
    return $response;
  }

  /**
   * Format a flat JSON string to make it more human-readable.
   *
   * @from https://github.com/GerHobbelt/nicejson-php/blob/master/nicejson.php
   *
   * @param string $json
   *   The original JSON string to process
   *   When the input is not a string it is assumed the input is RAW
   *        and should be converted to JSON first of all.
   *
   * @return string Indented version of the original JSON string
   */
  private function jsonPrettyPrint($json) {
    if (!is_string($json)) {
      if (phpversion() && phpversion() >= 5.4) {
        return json_encode($json, JSON_PRETTY_PRINT);
      }
      $json = json_encode($json);
    }
    $result = '';
    // Indentation level.
    $pos         = 0;
    $strLen      = strlen($json);
    $indentStr   = '&nbsp;&nbsp;&nbsp;';
    $newLine     = "\n";
    $prevChar    = '';
    $outOfQuotes = TRUE;
    for ($i = 0; $i < $strLen; $i++) {
      // Speedup: copy blocks of input which don't matter re string detection and formatting.
      $copyLen = strcspn($json, $outOfQuotes ? " \t\r\n\",:[{}]" : "\\\"", $i);
      if ($copyLen >= 1) {
        $copyStr = substr($json, $i, $copyLen);
        // Also reset the tracker for escapes: we won't be hitting any right now
        // and the next round is the first time an 'escape' character can be seen again at the input.
        $prevChar = '';
        $result .= $copyStr;
        // Correct for the for(;;) loop.
        $i += $copyLen - 1;
        continue;
      }

      // Grab the next character in the string.
      $char = substr($json, $i, 1);

      // Are we inside a quoted string encountering an escape sequence?
      if (!$outOfQuotes && $prevChar === '\\') {
        // Add the escaped character to the result string and ignore it for the string enter/exit detection:
        $result .= $char;
        $prevChar = '';
        continue;
      }
      // Are we entering/exiting a quoted string?
      if ($char === '"' && $prevChar !== '\\') {
        $outOfQuotes = !$outOfQuotes;
      }
      // If this character is the end of an element,
      // output a new line and indent the next line.
      elseif ($outOfQuotes && ($char === '}' || $char === ']')) {
        $result .= $newLine;
        $pos--;
        for ($j = 0; $j < $pos; $j++) {
          $result .= $indentStr;
        }
      }
      // Eat all non-essential whitespace in the input as we do our own here and it would only mess up our process.
      elseif ($outOfQuotes && FALSE !== strpos(" \t\r\n", $char)) {
        continue;
      }
      // Add the character to the result string.
      $result .= $char;
      // Always add a space after a field colon:
      if ($outOfQuotes && $char === ':') {
        $result .= ' ';
      }
      // If the last character was the beginning of an element,
      // output a new line and indent the next line.
      elseif ($outOfQuotes && ($char === ',' || $char === '{' || $char === '[')) {
        $result .= $newLine;
        if ($char === '{' || $char === '[') {
          $pos++;
        }
        for ($j = 0; $j < $pos; $j++) {
          $result .= $indentStr;
        }
      }
      $prevChar = $char;
    }
    return $result;
  }

}
