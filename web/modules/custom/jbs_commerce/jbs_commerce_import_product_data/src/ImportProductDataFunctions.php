<?php

namespace Drupal\jbs_commerce_import_product_data;

use Drupal\commerce_product\Entity\Product;

/**
 *
 */
class ImportProductDataFunctions implements ImportProductDataFunctionsInterface {

  /**
   *
   */
  public function importData() {
    $map_tn = 'commerce_product_variation_field_data';
    $db = \Drupal::database();

    try {
      $file = realpath('modules/custom/jbs_commerce/jbs_commerce_import_product_data/src/Products--PublicationCategoryPublicationAudience.20200204.csv');
      $map = [];
      $map_titles = [];

      $table_values = $db->select($map_tn, 'fd')
        ->fields('fd', ['sku', 'title', 'product_id'])
        ->execute()->fetchAll();

      foreach ($table_values as $p => $product) {
        $map[$product->sku] = $product->product_id;
        $map_titles[md5($product->title)] = $product->product_id;
      }

      $columns = [];
      $rows = [];
      $missing = [];

      if (($handle = fopen($file, 'r')) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
          // SKU, Title, Substance Abuse, Mental Health, Practitioner/Professional, General Public.
          $row = [$data[0], $data[1], $data[4], $data[5], $data[6], $data[7]];
          if (empty($columns)) {
            $columns = $row;
          }
          else {
            $id = (!empty($map[$row[0]]) ? $map[$row[0]] : $map_titles[md5($row[1])]);
            if (!empty($id)) {
              $product = Product::load($id);
              $checked_pub_category = [];
              $checked_pub_target_audience = [];

              if (!empty($row[2])) {
                // Substance Abuse.
                $checked_pub_category[] = ['target_id' => '5620'];
              }
              if (!empty($row[3])) {
                // Mental Health.
                $checked_pub_category[] = ['target_id' => '5621'];
              }
              if (!empty($row[4])) {
                // Practitioner/Professional.
                $checked_pub_target_audience[] = ['target_id' => '6037'];
              }
              if (!empty($row[5])) {
                // General Public.
                $checked_pub_target_audience[] = ['target_id' => '6038'];
              }

              if (!empty($checked_pub_category) || !empty($checked_pub_target_audience)) {
                if (!empty($checked_pub_category)) {
                  $product->set('field_publication_category', $checked_pub_category);
                }
                if (!empty($checked_pub_target_audience)) {
                  $product->set('field_pub_target_audience', $checked_pub_target_audience);
                }
                // $rows[] = $row;
                $product->save();
              }
            }
            else {
              $missing[] = $row;
            }
          }
        }
      }
      fclose($handle);
      \Drupal::messenger()->addMessage('Data was imported, check the corresponding tables.');
    }
    catch (Exception $e) {
      \Drupal::messenger()->addWarning('Import failed.');
    }
  }

}
