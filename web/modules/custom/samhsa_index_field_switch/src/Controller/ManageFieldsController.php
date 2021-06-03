<?php

namespace Drupal\samhsa_index_field_switch\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * Class ManageFieldsController.
 */
class ManageFieldsController extends ControllerBase {

  /**
   * Content.
   *
   * @return string
   *   Return Hello string.
   */
  public function content() {

    $current_path = \Drupal::service('path.current')->getPath();

    $list = $this->config('samhsa_index_field_switch.configuration')->get('list');
    if (!$list) {
      $list = [];
    }
    else {
      foreach ($list as &$item) {
        unset($item['from_field_type']);
        unset($item['from_field_format']);
        unset($item['to_field_type']);
        unset($item['to_field_format']);
      }
    }

    $operations = [
      'edit' => [
        'title' => $this->t('Edit'),
      ],
      'delete' => [
        'title' => $this->t('Delete'),
      ],
    ];

    foreach ($list as $key => &$item) {

      $edit_url = Url::fromRoute('samhsa_index_field_switch.edit_field_switch_form', [
        'item_id' => $key,
        'destination' => $current_path,
      ]);
      $edit_url->setOptions([
        'attributes' => [
          'class' => ['use-ajax', 'button', 'button--small'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode(['width' => 460]),
        ],
      ]);
      $operations['edit']['url'] = $edit_url;

      $item_info = $item + ['item_id' => $key];
      $delete_url = Url::fromRoute('samhsa_index_field_switch.delete_field_switch_form', [
        'item_info' => serialize($item_info),
        'destination' => $current_path,
      ]);
      $delete_url->setOptions([
        'attributes' => [
          'class' => ['use-ajax', 'button', 'button--small'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode(['width' => 460]),
        ],
      ]);
      $operations['delete']['url'] = $delete_url;

      $item[] = [
        'data' => [
          '#type' => 'operations',
          '#links' => $operations,
        ],
      ];

    }

    $build['list'] = [
      '#theme' => 'table',
      '#header' => ['Server', 'Index', 'From Field', 'To Field', 'Operation'],
      '#rows' => $list,
      '#wrapper_attributes' => ['class' => 'container'],
      '#weight' => 130,
    ];

    $add_url = Url::fromRoute('samhsa_index_field_switch.add_new_field_switch_form', ['destination' => $current_path]);
    $add_url->setOptions([
      'attributes' => [
        'class' => [
          'use-ajax',
          'button',
          'button-action',
          'button--primary',
          'button--small',
        ],
        'data-dialog-type' => 'modal',
        'data-dialog-options' => Json::encode(['width' => 460]),
      ],
    ]);

    $build['add_new_field'] = [
      '#type' => 'link',
      '#title' => t('Add new field switching'),
      '#url' => $add_url,
      '#prefix' => '<div id="te-custom-fields-add-button">',
      '#suffix' => '</div>',
      '#weight' => 100,
    ];

    return $build;

  }

}
