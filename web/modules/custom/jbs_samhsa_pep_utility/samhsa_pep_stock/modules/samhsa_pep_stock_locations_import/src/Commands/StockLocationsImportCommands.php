<?php

namespace Drupal\samhsa_pep_stock_locations_import\Commands;

use Drupal\commerce_product\Entity\Product;
use Drush\Commands\DrushCommands;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_stock\StockTransactionsInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 */
class StockLocationsImportCommands extends DrushCommands {

  /**
   * Import stock locations.
   *
   * @command samhsa_pep_stock_locations_import:import_stock_locations
   * @aliases import-stock-locations
   * @usage samhsa_pep_stock_locations_import:import_stock_locations
   */
  public function import_stock_locations() {
    $moduleDir = \Drupal::service('extension.list.module')
      ->getPath('samhsa_pep_stock_locations_import');
    $file = $moduleDir . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'Location_Report.csv';
    $path = __DIR__ . '/' . $file;
    $this->output()->writeln($file);
    if (file_exists($file)) {
      $this->output()->writeln('file exists');
      $data = $this->csvtoarray_locations($file, ',');
      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
      if (is_array($data) && count($data) > 0) {
        $count = 0;
        foreach ($data as $key => $l) {
          $this->output()->writeln($l);
          if ($terms = taxonomy_term_load_multiple_by_name($l, 'pallet_location')) {
            // Only use the first term returned; there should only be one anyways if we do this right.
            $term = reset($terms);
          }
          else {
            $count++;
            $term = Term::create([
              'name' => $l,
              'vid' => 'pallet_location',
            ]);
            $term->save();
          }
          $tid = $term->id();
        }
        $this->output()->writeln($count . ' locations imported');
      }
    }
  }

  /**
   * Associate locations with products.
   *
   * @command samhsa_pep_stock_locations_import:associate_locations
   * @aliases associate-locations
   * @usage samhsa_pep_stock_locations_import:associate_locations
   */
  public function associate_locations() {
    $moduleDir = \Drupal::service('extension.list.module')
      ->getPath('samhsa_pep_stock_locations_import');
    $file = $moduleDir . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'Location_Report.csv';
    $path = __DIR__ . '/' . $file;

    if (file_exists($file)) {
      $data = $this->csvtoarray_all($file, ',');
      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
      if ($data) {
        $count = 0;
        foreach ($data as $key => $l) {
          $count++;
          if ($count > 1) {
            $pub_id = $l[0];
            $location = $l[2];
            if ($terms = taxonomy_term_load_multiple_by_name($location, 'pallet_location')) {
              // Only use the first term returned; there should only be one anyways if we do this right.
              $term = reset($terms);
              // $this->output()->write("location_id: " . $term->id());
              $location_id = $term->id();
              $products = $this->getProdBySKU(trim($pub_id));
              $message = "Associate publication " . $pub_id . " with location " . $location;
              $metadata = [
                'data' => [
                  'message' => $message,
                ],
              ];
              if ($products) {
                foreach ($products as $row) {
                  $vid = $row['variation_id'];
                  $pid = $row['product_id'];
                  $sku = $row['sku'];
                  $this->output()->writeln("location_id: " . $term->id());
                  $this->output()->writeln("vid: " . $vid);
                  $this->output()->writeln("pid: " . $pid);
                  $this->output()->writeln("sku: " . $sku);
                  $this->output()
                    ->writeln("______________________________________");

                  $variation = ProductVariation::load($vid);
                  $stockServiceManager = \Drupal::service('commerce_stock.service_manager');

                  if (!is_null($variation)) {
                    \Drupal::logger('samhsa_pep_stock_locations_import')
                      ->notice('<pre><code>' . $message . '</code></pre>');
                    $stockServiceManager->createTransaction($variation, 1, $location_id, 0, 0, 'USD', StockTransactionsInterface::STOCK_IN, $metadata);
                  }

                }

              }
            }
            else {
              $this->output()->writeln("Term not found for: " . $location);
            }
          }
        }
      }
    }
  }

  /**
   * Remove association of locations with products.
   *
   * @command samhsa_pep_stock_locations_import:remove_locations_association
   * @aliases remove-association
   * @usage samhsa_pep_stock_locations_import:remove_locations_association
   */
  public function remove_association() {
    $moduleDir = \Drupal::service('extension.list.module')
      ->getPath('samhsa_pep_stock_locations_import');
    $file = $moduleDir . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'Location_Report.csv';
    $path = __DIR__ . '/' . $file;

    if (file_exists($file)) {
      $data = $this->csvtoarray_all($file, ',');
      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
      if ($data) {
        $count = 0;
        foreach ($data as $key => $l) {
          $count++;
          if ($count > 1) {
            $pub_id = $l[0];
            $location = $l[2];
            if ($terms = taxonomy_term_load_multiple_by_name($location, 'pallet_location')) {
              // Only use the first term returned; there should only be one anyways if we do this right.
              $term = reset($terms);
              // $this->output()->write("location_id: " . $term->id());
              $location_id = $term->id();
              $products = $this->getProdBySKU(trim($pub_id));
              $message = "Association removed between publication " . $pub_id . " and location " . $location;

              if ($products) {
                foreach ($products as $row) {
                  $vid = $row['variation_id'];
                  $pid = $row['product_id'];
                  $sku = $row['sku'];

                  $this->output()->writeln("vid: " . $vid);
                  $this->output()->writeln("pid: " . $pid);
                  $this->output()->writeln("sku: " . $sku);
                  $this->output()->writeln("location: " . $location);

                  $this->output()
                    ->writeln("______________________________________");

                  $this->removeLocation([trim($location_id)], $vid);
                  $this->output()->writeln($message);
                }
              }
            }
            else {
              $this->output()->writeln("Term not found for: " . $location);
            }
          }
        }
      }
    }
  }

  /**
   *
   */
  public function removeLocation(array $pallets, $variation_id) {
    $table_name = 'commerce_stock_transaction';
    $field = 'location_zone';
    foreach ($pallets as $key => $pallet) {
      $this->output()->writeln("location being removed: " . $pallet);
      \Drupal::database()
        ->update($table_name)
        ->condition('entity_id', $variation_id, '=')
        ->condition('location_zone', $pallet, '=')
        ->fields([
          'location_zone' => "",
          // 'some_other_field' => 20,
        ])
        ->execute();
    }
  }

  /**
   *
   */
  public function getProdBySKU($sku = '') {
    $results_list = [];
    $query = \Drupal::database()
      ->select('commerce_product_variation_field_data', 't');
    $query->fields('t', ['variation_id', 'product_id', 'sku']);
    $query->condition('sku', $sku, '=');
    $query->distinct(TRUE);
    $results = $query->orderBy('variation_id', 'ASC')->execute();
    $results->allowRowCount = TRUE;

    // Get redirects if anything set for the given node.
    if ($results->rowCount()) {
      $results_list = [];

      while ($record = $results->fetchAssoc()) {
        $results_list[] = $record;
      }
      // ksm($results_list);
      return $results_list;
    }
  }

  /**
   *
   */
  public function csvtoarray_locations($filename, $delimiter) {
    $locations = [];
    if (($handle = fopen($filename, "r")) !== FALSE) {
      // Loop through the CSV rows.
      while (($row = fgetcsv($handle, 0, ",")) !== FALSE) {
        // Print out my column data.
        if ($row == 1) {
          continue;
        }
        $locations[] = $row[2];
      }
    }
    // Unique array values.
    $unique_locations = array_unique($locations);

    return array_filter($unique_locations);
  }

  /**
   *
   */
  public function csvtoarray_all($filename, $delimiter) {
    $locations = [];
    if (($handle = fopen($filename, "r")) !== FALSE) {
      // Loop through the CSV rows.
      while (($row = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
        // Print out my column data.
        if ($row == 1) {
          continue;
        }
        $prod_locations[] = $row;
      }
    }

    return array_filter($prod_locations);
  }

  /**
   * Assign pick area locations to eligible products.
   *
   * @command samhsa_pep_stock_locations_import:assign_pickarea_location
   * @aliases assign-pickarea
   * @usage samhsa_pep_stock_locations_import:assign_pickarea_location
   */
  public function assign_pickarea_location() {
    // Get all eligible products.
    $query = \Drupal::entityQuery('commerce_product');
    $query->condition('status', 1);
    $orGroup = $query->orConditionGroup()
      ->condition('field_pep_product_type', 'order_only')
      ->condition('field_pep_product_type', 'download_order')
      ->condition('field_pep_product_type', 'samhsa_only');
    $query->condition($orGroup);
    $entity_prod = $query->execute();
    $location = 'Pick Area';
    if ($terms = taxonomy_term_load_multiple_by_name($location, 'pallet_location')) {
      // Only use the first term returned; there should only be one anyways if we do this right.
      $term = reset($terms);
      $this->output()->writeln("location_id: " . $term->id());
      $location_id = $term->id();
      foreach ($entity_prod as $key => $prod) {
        $this->output()->write($prod);
        /*Load Product Variations*/
        $product = Product::load((int) $prod);
        $variation_id = $product->getVariationIds()[0];

        $this->output()->writeln("prod variation: " . $variation_id);
        $variation = ProductVariation::load($variation_id);
        $pub_id = $variation->getSku();
        $stockServiceManager = \Drupal::service('commerce_stock.service_manager');
        $products = $this->getProdBySKU(trim($pub_id));
        $message = $pub_id . ": add default location: " . $location;
        $metadata = [
          'data' => [
            'message' => $message,
          ],
        ];
        $data = $metadata['data'] ?? NULL;

        if (!is_null($variation)) {
          \Drupal::logger('samhsa_pep_stock_locations_import')
            ->notice('<pre><code>' . $message . '</code></pre>');
          $stockServiceManager->createTransaction($variation, 1, $location_id, 0, 0, 'USD', StockTransactionsInterface::MOVEMENT_TO, $data);
        }
      }
      $this->output()
        ->writeln("\n___________________________________________________");
    }
  }

  /**
   * Remove pick area locations from not eligible products.
   *
   * @command samhsa_pep_stock_locations_import:remove_pickarea_location
   * @aliases remove-pickarea
   * @usage samhsa_pep_stock_locations_import:remove_pickarea_location
   */
  public function remove_pickarea_location() {
    // Get all eligible products.
    $query = \Drupal::entityQuery('commerce_product');
    $query->condition('status', 1);
    $query->condition('field_pep_product_type', 'download_only');
    $entity_prod = $query->execute();
    $location = 'Pick Area';
    if ($terms = taxonomy_term_load_multiple_by_name($location, 'pallet_location')) {
      // Only use the first term returned; there should only be one anyways if we do this right.
      $term = reset($terms);
      $this->output()->writeln("location_id: " . $term->id());
      $location_id = $term->id();
      $number_of_updated = 0;
      // If (!empty(\Drupal::hasService('samhsa_pep_stock.pep_stock_utility'))) {.
      foreach ($entity_prod as $key => $prod) {
        /*Load Product Variations*/
        $product = Product::load((int) $prod);
        $variation_id = $product->getVariationIds()[0];
        $this->output()->writeln("prod variation: " . $variation_id);

        $num_deleted = \Drupal::database()
          ->delete('commerce_stock_transaction')
          ->condition('entity_id', $variation_id)
          ->condition('location_zone', $location_id)
          ->condition('transaction_type_id', 8)
          ->condition('data', '%add default location%', "LIKE")
          ->execute();
        $number_of_updated += $num_deleted;
      }
      $this->output()
        ->writeln("Pick Area location removed from " . $number_of_updated . "product(s).");
      $this->output()
        ->writeln("\n___________________________________________________");
      // }
    }
  }

  /**
   * Revert pick area locations migration for all products.
   *
   * @command samhsa_pep_stock_locations_import:revert_pickarea_location_migration
   * @aliases revert-pickarea
   * @usage samhsa_pep_stock_locations_import:revert_pickarea_location_migration
   */
  public function revert_pickarea_location_migration() {
    $location = 'Pick Area';
    if ($terms = taxonomy_term_load_multiple_by_name($location, 'pallet_location')) {
      // Only use the first term returned; there should only be one anyways if we do this right.
      $term = reset($terms);
      $this->output()->writeln("location_id: " . $term->id());
      $location_id = $term->id();
      $number_of_updated = 0;
      $num_deleted = \Drupal::database()
        ->delete('commerce_stock_transaction')
        ->condition('location_zone', $location_id)
        ->condition('transaction_type_id', 8)
        ->condition('data', '%add default location%', "LIKE")
        ->execute();
      $number_of_updated += $num_deleted;

      $this->output()
        ->writeln("Pick Area location removed from " . $number_of_updated . " product(s).");
      $this->output()
        ->writeln("\n___________________________________________________");
    }
  }

  /**
   * Revert pick area locations migration for all products.
   *
   * @command samhsa_pep_stock_locations_import:update_product_pallets_location_field
   * @aliases update-product-pallets
   * @usage samhsa_pep_stock_locations_import:update_product_pallets_location_field
   */
  public function update_product_pallets_location_field() {
    // Get all variations.
    $query = \Drupal::entityQuery('commerce_product_variation');
    $variations = $query->execute();
    $number_of_updated = 0;
    foreach ($variations as $key => $value) {
      /*Load Product Variations*/
      $variation = ProductVariation::load((int) $value);
      $variation_id = $variation->id();

      $this->output()->writeln("prod variation: " . $variation_id);
      \Drupal::service('samhsa_pep_stock.pep_stock_utility')
        ->updateProductPallets($variation_id, $variation);
      $number_of_updated++;
    }
    $this->output()
      ->writeln("Number of Variations updated " . $number_of_updated);

  }

}
