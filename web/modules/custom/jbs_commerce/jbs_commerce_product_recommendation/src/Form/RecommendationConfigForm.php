<?php

/**
 * @file
 * Contains \Drupal\jbs_commerce_product_recommendation\Form\RecommendationConfigForm.
 */
namespace Drupal\jbs_commerce_product_recommendation\Form;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\jbs_commerce_product_recommendation\RecommendationModelFunctions;

class RecommendationConfigForm extends ConfigFormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'recommendation_config_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attributes']['onkeypress'] = 'if(event.keyCode==13){event.preventDefault();}';

    $form['refresh_model'] = [
      '#type' => 'details',
      '#title' => t('Refresh Recommendation Model'),
      '#open' => TRUE,
    ];
    $form['refresh_model']['button'] = [
      '#type' => 'submit',
      '#value' => t('Refresh Model'),
      '#submit' => ['::refreshModel'],
      '#attributes' => [
        'onclick' => 'if(!confirm("Refreshing the model may take a long time. Are you sure you want to continue?")){ return false; }',
      ],
    ];
    $form['refresh_model']['clear_button'] = [
      '#type' => 'submit',
      '#value' => t('Clear Model'),
      '#submit' => ['::clearModel'],
      '#attributes' => [
        'onclick' => 'if(!confirm("Warning: This option will clear all the current pairs data. Are you sure you want to continue?")){ return false; }',
      ],
    ];

    $form['edit_config'] = [
      '#type' => 'details',
      '#title' => t('Edit Config'),
      '#open' => TRUE,
    ];
    $form['edit_config']['autoUpdate'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable auto Refresh Model'),
      '#default_value' => \Drupal::config($this->getEditableConfigNames()[0])->get('autoUpdate'),
    ];
    $form['edit_config']['intervalUpdate'] = [
      '#type' => 'number',
      '#title' => t('Interval between Refresh Models in days:'),
      '#default_value' => \Drupal::config($this->getEditableConfigNames()[0])->get('intervalUpdate'),
      '#min' => 0,
      '#step' => 0.0001,
    ];
    $form['edit_config']['numberOfRecommendations'] = [
      '#type' => 'number',
      '#title' => t('Enter the number of recommendations shown per product:'),
      '#default_value' => \Drupal::config($this->getEditableConfigNames()[0])->get('numberOfRecommendations'),
      '#min' => 0,
    ];
    $form['edit_config']['save'] = [
      '#type' => 'submit',
      '#value' => t('Save Configuration'),
      '#submit' => ['::submitForm'],
      '#prefix' => '<span>',
      '#suffix' => '</span>',
    ];
    $form['edit_config']['reset'] = [
      '#type' => 'submit',
      '#value' => t('Reset Configuration'),
      '#submit' => ['::resetForm'],
    ];

    $form['query'] = [
      '#type' => 'details',
      '#title' => t('Query'),
      '#open' => TRUE,
    ];
    $form['query']['input_pid'] = [
      '#type' => 'number',
      '#title' => 'Enter a product ID:',
      '#ajax' => [
        'callback' => '::getTitle',
        'event' => 'change',
        'wrapper' => 'product-title',
      ],
      '#min' => 1,
      '#step' => 1,
    ];
    $form['query']['title_pid'] = [
      '#type' => 'container',
      '#open' => TRUE,
    ];
    $form['query']['title_pid']['result'] = [
      '#type' => 'hidden',
      '#id' => 'title-result',
      '#prefix' => '<div id="product-title">',
      '#suffix' => '</div>',
    ];
    $form['query']['getScores'] = [
      '#type' => 'button',
      '#value' => t('Get Scores'),
      '#ajax' => [
        'callback' => '::getScores',
        'wrapper' => 'ajax-container',
      ],
      '#prefix' => '<span>',
      '#suffix' => '</span>',
    ];

    $form['query']['getAllProducts'] = [
      '#type' => 'button',
      '#value' => t('Get All Products'),
      '#ajax' => [
        'callback' => '::getAllProducts',
        'wrapper' => 'ajax-container',
      ],
      '#prefix' => '<div>&nbsp;</div><div><span>',
      '#suffix' => '</span></div>',
    ];

    // #ajax wrapper corresponds to div id, inserts return value of callback into this container
    $form['container'] = [
      '#type' => 'details',
      '#title' => 'Query Results',
      '#open' => TRUE,
    ];
    $form['container']['results'] = [
      '#type' => 'hidden',
      '#id' => 'query-results',
      '#prefix' => '<div id="ajax-container">',
      '#suffix' => '</div>',
    ];

    return parent::buildForm($form, $form_state);
    //return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    if ($this->config($this->getEditableConfigNames()[0])->get('default.autoUpdate') === null &&
      $this->config($this->getEditableConfigNames()[0])->get('default.intervalUpdate') === null &&
      $this->config($this->getEditableConfigNames()[0])->get('default.numberOfRecommendations') === null) {
      $original = $this->config($this->getEditableConfigNames()[0])->getOriginal();
      $this->config($this->getEditableConfigNames()[0])
        ->set('default.autoUpdate', $original['autoUpdate'])
        ->set('default.intervalUpdate', $original['intervalUpdate'])
        ->set('default.numberOfRecommendations', $original['numberOfRecommendations'])
        ->save();
    }
    $this->config($this->getEditableConfigNames()[0])
      ->set('autoUpdate', $values['autoUpdate'])
      ->set('intervalUpdate', $values['intervalUpdate'])
      ->set('numberOfRecommendations', $values['numberOfRecommendations'])
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  protected function getEditableConfigNames() {
    return ['jbs_commerce_product_recommendation.settings'];
  }

  /**
   * Reset 'EDIT CONFIG' fields to the default values
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function resetForm(array &$form, FormStateInterface $form_state) {
    $original = $this->config($this->getEditableConfigNames()[0])->getOriginal();
    if ($this->config($this->getEditableConfigNames()[0])->get('default.autoUpdate') !== null &&
      $this->config($this->getEditableConfigNames()[0])->get('default.intervalUpdate') !== null &&
      $this->config($this->getEditableConfigNames()[0])->get('default.numberOfRecommendations') !== null) {
      $this->config($this->getEditableConfigNames()[0])
        ->set('autoUpdate', $original['default']['autoUpdate'])
        ->set('intervalUpdate', $original['default']['intervalUpdate'])
        ->set('numberOfRecommendations', $original['default']['numberOfRecommendations'])
        ->save();
      \Drupal::messenger()->addMessage('Default configuration options have been restored.');
      parent::submitForm($form, $form_state);
    }
  }

  /**
   * Recommendation Engine Functions
   */

  public function refreshModel() {
    (new RecommendationModelFunctions)->refreshModel();
  }

  public function clearModel() {
    (new RecommendationModelFunctions)->clearModel();
  }

  /**
   * getScores() callback returns the form element creation of the table
   * containing product pairs based on the input_pid given. This is called
   * when the button labeled 'Get Scores' is clicked.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array|mixed
   */
  public function getScores(array &$form, FormStateInterface $form_state) {
    $pid = $form['query']['input_pid']['#value'];
    $modelFunctionsInstance = (new RecommendationModelFunctions);
    $pairs = $modelFunctionsInstance->getScores($pid);
    $numberOfRecommendations = \Drupal::config('jbs_commerce_product_recommendation.settings')->get('numberOfRecommendations');

    $title = $this->getTitleQuery($pid);

    $recommendedPairs = (function () use ($pid, $numberOfRecommendations, $modelFunctionsInstance) {
      $recommended = array();
      $rows = $modelFunctionsInstance->getScores($pid, $numberOfRecommendations);
      foreach ($rows as $r => $row) {
        if ($row->p1 !== $pid) {
          $recommended[] = $row->p1;
        } else {
          $recommended[] = $row->p2;
        }
      }
      $result = array_fill_keys($recommended, 1);
      return $result;
    })();

    $form['table'] = [
      '#type' => 'hidden',
      '#prefix' => '<div id="ajax-container">',
      '#suffix' => '</div>',
    ];

    if (!empty($pairs)) {
      $headers = array_keys(get_object_vars($pairs[0]) + array('title' => null));
      $rows = array();

      for ($row = 0; $row < count($pairs); $row++) {
        if ($numberOfRecommendations > 0) {
          if (!empty($recommendedPairs[$pairs[$row]->{$headers[0]}]) || !empty($recommendedPairs[$pairs[$row]->{$headers[1]}])) {
            $recommendedColumn = ($pairs[$row]->{$headers[0]} !== $pid ? 0 : ($pairs[$row]->{$headers[1]} !== $pid ? 1 : null));
            $new_row = [
              ($pairs[$row]->{$headers[0]} !== $pid ? t('<span style="background:yellow">' . $pairs[$row]->{$headers[0]} . '</span>') : $pairs[$row]->{$headers[0]}),
              ($pairs[$row]->{$headers[1]} !== $pid ? t('<span style="background:yellow">' . $pairs[$row]->{$headers[1]} . '</span>') : $pairs[$row]->{$headers[1]}),
              $pairs[$row]->{$headers[2]},
              ($recommendedColumn !== null ? t('<a href="'.\Drupal::request()->getSchemeAndHttpHost(). '/product/'. $pairs[$row]->{$headers[$recommendedColumn]} .'">' . $this->getTitleQuery($pairs[$row]->{$headers[$recommendedColumn]})->title . '</a>') : null)
            ];
            $numberOfRecommendations -= 1;
          } else {
            $new_row = array($pairs[$row]->{$headers[0]}, $pairs[$row]->{$headers[1]}, $pairs[$row]->{$headers[2]}, null);
          }
        } else {
          $new_row = array($pairs[$row]->{$headers[0]}, $pairs[$row]->{$headers[1]}, $pairs[$row]->{$headers[2]}, null);
        }
        $rows[] = $new_row;
      }

      $form['table'] = [
        '#type' => 'table',
        '#header' => $headers,
        '#rows' => $rows,
        '#prefix' => '<div id="ajax-container"><h3>Showing results for Product '. $pid . ':</h3>
                      <a href="'.\Drupal::request()->getSchemeAndHttpHost(). '/product/'. $pid .'">'
                      . $title->title . '</a>' .
                      '<p>Note: Highlighted IDs are the products most likely being recommended for this product (assuming no manual recommendations have been set).</p>' .
                      '<div>&nbsp;</div>',
        '#suffix' => '</div>',
      ];
    } else {
      if (!empty($title)) {
        \Drupal::messenger()->addWarning('Product ' . $pid . ' (' . $title->title . ') currently has no pair scores.');
      } else {
        \Drupal::messenger()->addWarning('Product with id ' . $pid . ' does not exist.');
      }
    }
    return $form['table'];
  }

  /**
   * getTitle() callback returns the form element creation of the product title
   * based on the input_pid given. This is called when the textfield labeled
   * 'Enter a product ID:' changes.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array|mixed
   */
  public function getTitle(array &$form, FormStateInterface $form_state) {
    $pid = $form['query']['input_pid']['#value'];
    $title = $this->getTitleQuery($pid);

    if (!empty($title)) {
      $form['product_title'] = [
        '#type' => 'item',
        '#prefix' => '<div id="product-title">' .
          '<a href="'.\Drupal::request()->getSchemeAndHttpHost(). '/product/'. $pid.'">'. $title->title .'</a>',
        '#suffix' => '</div>',
      ];
    } else {
      $form['product_title'] = [
        '#type' => 'hidden',
        '#prefix' => '<div id="product-title">',
        '#suffix' => '</div>',
      ];
    }

    return $form['product_title'];
  }

  /**
   * getTitleQuery() queries the database to get the title of a product by id
   *
   * @param $pid
   *
   * @return mixed
   */
  public function getTitleQuery($pid) {
    $title = \Drupal::database()->select('commerce_product_field_data', 'cd')
      ->fields('cd', ['product_id', 'title'])
      ->condition('product_id', $pid, '=')
      ->range(0, 1)
      ->execute()->fetchAll();
    return $title[0];
  }

  /**
   * getAllProducts() callback returns the form element creation of the table
   * containing all products. This is called when the button labeled
   * 'Get All Products' is clicked.
   *
   * @return array|mixed
   */
  public function getAllProducts() {
    $title = \Drupal::database()->select('commerce_product_field_data', 'cd')
      ->fields('cd', ['product_id', 'title'])
      ->execute()->fetchAll();

    $form['table'] = [
      '#type' => 'hidden',
      '#prefix' => '<div id="ajax-container">',
      '#suffix' => '</div>',
    ];

    if (!empty($title)) {
      $headers = ['product_id', 'title'];
      $rows = array();

      for ($row = 0; $row < count($title); $row++) {
        $new_row = array($title[$row]->{$headers[0]},
          t('<a href="'.\Drupal::request()->getSchemeAndHttpHost(). '/product/'. $title[$row]->{$headers[0]}.'">' . $title[$row]->{$headers[1]} . '</a>'));
        $rows[] = $new_row;
      }

      $form['table'] = [
        '#type' => 'table',
        '#header' => $headers,
        '#rows' => $rows,
        '#prefix' => '<div id="ajax-container"><h3>Showing IDs and Titles for all Products ('. count($rows) .' total)</h3><div>&nbsp;</div>',
        '#suffix' => '</div>',
      ];
    } else {
      \Drupal::messenger()->addWarning('There was an error retrieving Product information.');
    }
    return $form['table'];
  }

}
