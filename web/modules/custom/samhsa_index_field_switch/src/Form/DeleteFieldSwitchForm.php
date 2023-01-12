<?php

namespace Drupal\samhsa_index_field_switch\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Class DeleteFieldSwitchForm.
 */
class DeleteFieldSwitchForm extends ConfirmFormBase {

  private $itemInfo = [];

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'delete_field_switch_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $item_info = NULL) {

    $this->itemInfo = unserialize($item_info);

    $form['server_id'] = [
      '#type' => 'item',
      '#title' => t('Server'),
      '#plain_text' => $this->itemInfo['server_id'],
      '#value' => $this->itemInfo['server_id'],
    ];
    $form['index_id'] = [
      '#type' => 'item',
      '#title' => t('Index'),
      '#plain_text' => $this->itemInfo['index_id'],
      '#value' => $this->itemInfo['index_id'],
    ];
    $form['from_field'] = [
      '#type' => 'item',
      '#title' => t('From field'),
      '#plain_text' => $this->itemInfo['from_field'],
      '#value' => $this->itemInfo['from_field'],
    ];
    $form['to_field'] = [
      '#type' => 'item',
      '#title' => t('To fiels'),
      '#plain_text' => $this->itemInfo['to_field'],
      '#value' => $this->itemInfo['to_field'],
    ];
    $form['item_id'] = [
      '#type' => 'value',
      '#value' => $this->itemInfo['item_id'],
    ];

    return parent::buildForm($form, $form_state);

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $item_id = $form_state->getValue('item_id');
    $config = \Drupal::service('config.factory')->getEditable('samhsa_index_field_switch.configuration');
    $list = $config->get('list');
    unset($list[$item_id]);
    $config->set('list', $list)->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('samhsa_index_field_switch.configuration_controller');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Do you want to delete this field switching?');
  }

}
