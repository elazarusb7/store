<?php

/**
 * @file
 * Contains \Drupal\jbs_commerce_product_recommendation\Form\FeaturedProductForm.
 */
namespace Drupal\jbs_commerce_featured_product\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Exception;

class FeaturedProductForm extends ConfigFormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'featured_product_config_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attributes']['onkeypress'] = 'if(event.keyCode==13){event.preventDefault();}';

    $numberFeatured = $this->config($this->getEditableConfigNames()[0])->get('featured_number');

    $rows = array();
    $columns = ['Featured Products', 'Order'];

    $form['edit_config'] = [
      '#type' => 'details',
      '#title' => t('Edit Config'),
      '#open' => TRUE,
    ];
    $form['edit_config']['featured_number'] = [
      '#type' => 'number',
      '#title' => 'Set Number of Featured Products:',
      '#min' => 0,
      '#max' => 100,
      '#step' => 1,
      '#default_value' => $numberFeatured,
//      '#suffix' => '<br>',
    ];
    $form['edit_config']['save'] = [
      '#type' => 'submit',
      '#value' => t('Save configuration'),
      '#submit' => ['::submitForm'],
      '#prefix' => '<span>',
      '#suffix' => '</span>',
    ];
    $form['edit_config']['reset'] = [
      '#type' => 'submit',
      '#value' => t('Reset configuration'),
      '#submit' => ['::resetForm'],
    ];

    $form['featured_product'] = [
      '#type' => 'table',
      '#header' => $columns,
//      '#attributes' => [
//        'id' => 'featured_products',
//      ],
    ];

    if (!empty($numberFeatured)) {
      $featured = $this->config($this->getEditableConfigNames()[0])->get('featured');
      for ($i = 0; $i < $numberFeatured; $i++) {
        $form['featured_product'][$i]['featured'] = [
          '#type' => 'entity_autocomplete',
          '#target_type' => 'commerce_product',
          '#default_value' => (!empty($featured[$i]) ? \Drupal\commerce_product\Entity\Product::load((int)$featured[$i]['featured']) : NULL),
          '#maxlength' => 1024,
        ];
        $form['featured_product'][$i]['order'] = [
          '#type' => 'select',
          '#default_value' => $i,
          '#options' => range(1, $numberFeatured),
        ];
      }
    }
    return parent::buildForm($form, $form_state);
    //return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $order = $values['featured_product'];
    usort($order, function ($a, $b) {
      return $a['order'] <=> $b['order'];
    });
    $featured = array_slice($order, 0, $values['featured_number']);
    if ($this->setFeatured($featured)) {
      $this->config($this->getEditableConfigNames()[0])
        ->set('featured_number', $values['featured_number'])
        ->clear('featured')
        ->set('featured', $featured)
        ->save();
      parent::submitForm($form, $form_state);
    } else {
      \Drupal::messenger()->addError('Error: could not add products to featured, check your input.');
    }
  }

  /**
   * {@inheritDoc}
   */
  protected function getEditableConfigNames() {
    return ['jbs_commerce_featured_product.settings'];
  }

  public function resetForm(array &$form, FormStateInterface $form_state) {
    $original = $this->config($this->getEditableConfigNames()[0])->getOriginal();
    if ($this->config($this->getEditableConfigNames()[0])->get('default.featured_number') !== null) {
      $this->config($this->getEditableConfigNames()[0])
        ->set('featured_number', $original['default']['featured_number'])
        ->save();
      \Drupal::messenger()->addMessage('Default configuration options have been restored.');
      parent::submitForm($form, $form_state);
    }
  }

  public function setFeatured($featured) {
    $tn = 'commerce_product__field_featured';
    $db = \Drupal::database();
    try {
      $table_values = $db->select($tn, 't')
        ->fields('t', ['entity_id'])
        ->execute()->fetchAll();
      foreach ($table_values as $f => $feature) {
        $product = \Drupal\commerce_product\Entity\Product::load($feature->entity_id);
        unset($product->field_featured);
        $product->save();
      }

      $featured_products = array();
      $featured_count = count($featured);
      foreach ($featured as $f => $feature) {
        if (!empty($feature['featured'])) {
          $product = \Drupal\commerce_product\Entity\Product::load($feature['featured']);
          $product->set('field_featured', $featured_count - (int)$feature['order'] /*+ 1*/);
          $featured_products[] = $product;
        }
      }
      foreach ($featured_products as $p => $product) {
        $product->save();
      }
      return true;
    } catch (Exception $e) {
      return false;
    }
  }

}
