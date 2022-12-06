<?php

namespace Drupal\samhsa_index_field_switch\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\search_api\Entity\Server;

/**
 * Class AddNewFieldSwitchForm.
 */
class AddNewFieldSwitchForm extends FormBase {

  /**
   * Basic types for typecasting.
   *
   * @var array
   */
  private $fieldTypes = [
    'string' => 'string',
    'date' => 'date',
    'timestamp' => 'timestamp',
    'number' => 'number',
  ];

  /**
   * Id of the item.
   *
   * @var int
   */
  private $itemId = 0;

  /**
   * Default values.
   *
   * @var array
   */
  private $defaultValues = [];

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'add_new_field_switch_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $item_id = NULL) {

    $config = $this->config('samhsa_index_field_switch.configuration');
    $list = $config->get('list');

    $this->itemId = $item_id;
    if ($item_id === NULL) {
      $this->defaultValues = [
        'server_id' => NULL,
        'index_id' => NULL,
        'from_field' => NULL,
        'from_field_type' => NULL,
        'from_field_format' => NULL,
        'to_field' => NULL,
        'to_field_type' => NULL,
        'to_field_format' => NULL,
      ];
    }
    else {
      $this->defaultValues = $list[$item_id];
    }

    $weight = 0;
    $servers = [];
    foreach (search_api_solr_get_servers() as $server) {
      $this->buildFieldsForm($form, $weight, $servers, $server);
    }

    $form['servers_fields']['server'] = [
      '#type' => 'radios',
      '#title' => $this->t('Servers to be used used on searches'),
      '#options' => $servers,
      '#default_value' => $this->defaultValues['server_id'],
      '#weight' => -100,
      '#prefix' => '<div id="ipd-server-radios-wrapper">',
      '#suffix' => '</div>',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#weight' => 100,
    ];

    return $form;
  }

  /**
   * Generates the input fields for a specif server.
   *
   * @param array $form
   *   Form array.
   * @param int $weight
   *   The weight of the fieldset element.
   * @param array $servers
   *   Associative list: server id => server name.
   * @param \Drupal\search_api\Entity\Server $server
   *   Server object.
   */
  private function buildFieldsForm(array &$form, &$weight, array &$servers, Server $server) {

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
      '#default_value' => $server_id == $this->defaultValues['server_id'] ? $this->defaultValues['index_id'] : NULL,
    ];

    $field_names = \Drupal::service('samhsa_te_solr_connections')
      ->getIndexedFieldNames($server_id);
    foreach ($field_names as &$field_name) {
      $field_name = str_replace(["\n", "\r"], NULL, $field_name);
    }
    $field_names = array_combine($field_names, $field_names);

    // From field.
    $form['servers_fields'][$server_id]['from_info'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('From field'),
    ];
    $form['servers_fields'][$server_id]['from_info']['from_field_' . $server_id] = [
      '#type' => 'select',
      '#title' => $this->t('Name'),
      '#options' => $field_names,
      '#default_value' => $server_id == $this->defaultValues['server_id'] ? $this->defaultValues['from_field'] : NULL,
    ];
    $form['servers_fields'][$server_id]['from_info']['from_field_type_' . $server_id] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#options' => $this->fieldTypes,
      '#default_value' => $server_id == $this->defaultValues['server_id'] ? $this->defaultValues['from_field_type'] : NULL,
    ];
    $form['servers_fields'][$server_id]['from_info']['from_field_format_' . $server_id] = [
      '#type' => 'textfield',
      '#title' => $this->t('Format'),
      '#size' => 30,
      '#maxlength' => 30,
      '#default_value' => $server_id == $this->defaultValues['server_id'] ? $this->defaultValues['from_field_format'] : NULL,
    ];

    // To field.
    $form['servers_fields'][$server_id]['to_info'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('To field'),
    ];
    $form['servers_fields'][$server_id]['to_info']['to_field_' . $server_id] = [
      '#type' => 'select',
      '#title' => $this->t('Name'),
      '#options' => $field_names,
      '#default_value' => $server_id == $this->defaultValues['server_id'] ? $this->defaultValues['to_field'] : NULL,
    ];
    $form['servers_fields'][$server_id]['to_info']['to_field_type_' . $server_id] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#options' => $this->fieldTypes,
      '#default_value' => $server_id == $this->defaultValues['server_id'] ? $this->defaultValues['to_field_type'] : NULL,
    ];
    $form['servers_fields'][$server_id]['to_info']['to_field_format_' . $server_id] = [
      '#type' => 'textfield',
      '#title' => $this->t('Format'),
      '#size' => 30,
      '#maxlength' => 30,
      '#default_value' => $server_id == $this->defaultValues['server_id'] ? $this->defaultValues['to_field_format'] : NULL,
    ];

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = \Drupal::service('config.factory')->getEditable('samhsa_index_field_switch.configuration');
    $list = $config->get('list');
    foreach (array_keys($config->getRawData()) as $key) {
      $config->clear($key);
    }
    $values = $form_state->getValues();
    $item = [
      'server_id' => $values['server'],
      'index_id' => $values['index_' . $values['server']],
      'from_field' => $values['from_field_' . $values['server']],
      'from_field_type' => $values['from_field_type_' . $values['server']],
      'from_field_format' => $values['from_field_format_' . $values['server']],
      'to_field' => $values['to_field_' . $values['server']],
      'to_field_type' => $values['to_field_type_' . $values['server']],
      'to_field_format' => $values['to_field_format_' . $values['server']],
    ];
    if ($this->itemId === NULL) {
      $list[] = $item;
    }
    else {
      $list[$this->itemId] = $item;
    }

    $config->set('list', $list)->save();

  }

}
