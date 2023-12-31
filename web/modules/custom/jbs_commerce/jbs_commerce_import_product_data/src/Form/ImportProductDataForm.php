<?php

namespace Drupal\jbs_commerce_import_product_data\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\jbs_commerce_import_product_data\ImportProductDataFunctions;

/**
 *
 */
class ImportProductDataForm extends FormBase {

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    // @todo Implement getEditableConfigNames() method.
  }

  /**
   * Returns a unique string identifying the form.
   *
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'import_product_data_config_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['import'] = [
      '#type' => 'submit',
      '#value' => t('Import Product Data'),
      '#submit' => ['::importData'],
    ];
    return $form;
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // @todo Implement submitForm() method.
  }

  /**
   *
   */
  public function importData() {
    /*
    $tn = 'commerce_product__field_pub_target_audience';
    $map_tn = 'commerce_product_variation_field_data';
    $db = \Drupal::database();

    try {
    $file = realpath('modules/custom/jbs_commerce/jbs_commerce_import_product_data/src/Products--PublicationCategoryPublicationAudience.20200204.csv');
    $map = array();
    $map_titles = array();

    $table_values = $db->select($map_tn, 'fd')
    ->fields('fd', ['sku', 'title', 'product_id'])
    ->execute()->fetchAll();

    foreach ($table_values as $p => $product) {
    $map[$product->sku] = $product->product_id;
    $map_titles[md5($product->title)] = $product->product_id;
    }

    $columns = array();
    $rows = array();
    $missing = array();

    if (($handle = fopen($file, 'r')) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
    $row = [$data[0], $data[1], $data[4], $data[5], $data[6], $data[7]]; // SKU, Title, Substance Abuse, Mental Health, Practitioner/Professional, General Public
    if (empty($columns)) {
    $columns = $row;
    } else {
    $id = (!empty($map[$row[0]]) ? $map[$row[0]] : $map_titles[md5($row[1])]);
    if (!empty($id)) {
    $product = \Drupal\commerce_product\Entity\Product::load($id);
    $checked_pub_category = array();
    $checked_pub_target_audience = array();

    if (!empty($row[2])) {
    $checked_pub_category[] = ['target_id' => '5620']; // Substance Abuse
    }
    if (!empty($row[3])) {
    $checked_pub_category[] = ['target_id' => '5621']; // Mental Health
    }
    if (!empty($row[4])) {
    $checked_pub_target_audience[] = ['target_id' => '6037']; // Practitioner/Professional
    }
    if (!empty($row[5])) {
    $checked_pub_target_audience[] = ['target_id' => '6038']; // General Public
    }

    if (!empty($checked_pub_category) || !empty($checked_pub_target_audience)) {
    if (!empty($checked_pub_category)) {
    $product->set('field_publication_category', $checked_pub_category);
    }
    if (!empty($checked_pub_target_audience)) {
    $product->set('field_pub_target_audience', $checked_pub_target_audience);
    }
    //$rows[] = $row;
    $product->save();
    }
    } else {
    $missing[] = $row;
    }
    }
    }
    }
    fclose($handle);
    \Drupal::messenger()->addMessage('Data was imported, check the corresponding tables.');
    } catch (Exception $e) {
    \Drupal::messenger()->addWarning('Import failed.');
    }
     */
    (new ImportProductDataFunctions)->importData();
  }

}
