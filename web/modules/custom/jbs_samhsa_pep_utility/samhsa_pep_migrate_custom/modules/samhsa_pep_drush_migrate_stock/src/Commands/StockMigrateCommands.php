<?php

namespace Drupal\samhsa_pep_drush_migrate_stock\Commands;

use Drupal\Core\Database\Database;
use Drush\Commands\DrushCommands;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_stock\StockTransactionsInterface;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 */
class StockMigrateCommands extends DrushCommands {

  /**
   * Echos back hello with the argument provided.
   *
   * @param string $name
   *   Argument provided to the drush command.
   *
   * @command samhsa_pep_drush_migrate_stock:hello
   * @aliases d9-hello2
   * @options arr An option that takes multiple values.
   * @options msg Whether or not an extra message should be displayed to the user.
   * @usage samhsa_pep_drush_migrate_stock:hello akanksha --msg
   *   Display 'Hello Akanksha!' and a message.
   */
  public function hello($name, $options = ['msg' => FALSE]) {
    if ($options['msg']) {
      $this->output()->writeln('Hello ' . $name . '! This is your first Drush 9 command.');
    }
    else {
      $this->output()->writeln('Hello ' . $name . '!');
    }
  }

  /**
   * Migrate stock values from source to destination database.
   *
   * @command samhsa_pep_drush_migrate_stock:migrate_stock
   * @aliases migrate-stock
   * @usage samhsa_pep_drush_migrate_stock:migrate_stock
   */
  public function migrate_stock() {
    // Switch to external database.
    Database::setActiveConnection('migrate');
    // Get a connection going.
    $db = Database::getConnection();
    $getConnectionOptions = $db->getConnectionOptions();
    $d7 = $getConnectionOptions['database'];
    $d7_prefix = $getConnectionOptions['prefix']['default'];
    // stock.
    $query = 'SELECT entity_id, commerce_stock_value
        FROM ' . $d7 . '.field_data_commerce_stock
        WHERE bundle = :bundle';
    $results = $db->query($query, [':bundle' => 'samhsa_publications']);
    $values = [];
    foreach ($results as $record) {
      $values[$record->entity_id] = $record->commerce_stock_value;
    }

    // Switch back.
    Database::setActiveConnection();
    foreach ($values as $key => $value) {
      $stockServiceManager = \Drupal::service('commerce_stock.service_manager');
      /*$variations = \Drupal::entityQuery('commerce_product_variation')
      //->condition('sku', 'MYSKU')
      ->condition('variation_id', $key)
      ->execute();
      $variation = ProductVariation::load(reset($variations));*/

      $variation = ProductVariation::load($key);
      if (!is_null($variation)) {

        // \Drupal::logger('samhsa_pep_migrate_custom')->notice('<pre><code>' . $variation->getSku() . '</code></pre>');
        $stockServiceManager->createTransaction($variation, 1, '', $value, 0, 'USD', StockTransactionsInterface::STOCK_IN, ['data' => []]);
      }
      // \Drupal::logger('samhsa_pep_migrate_custom')->notice('<pre><code>' . $variation->getSku() . '</code></pre>');
      $this->output()->writeln(count($values) . ' commerce stock transactions records were migrated.');
    }
  }

  /**
   * Rollback stock values.
   *
   * @command samhsa_pep_drush_migrate_stock:rollback_commerce_stock
   * @aliases rollback-stock
   * @usage samhsa_pep_drush_migrate_stock:rollback_commerce_stock
   */
  public function rollback_commerce_stock() {
    // Switch to external database.
    Database::setActiveConnection('migrate');
    // Get a connection going.
    $db = Database::getConnection();
    $getConnectionOptions = $db->getConnectionOptions();
    $d7 = $getConnectionOptions['database'];
    $d7_prefix = $getConnectionOptions['prefix']['default'];
    // stock.
    $query = 'SELECT entity_id, commerce_stock_value
        FROM ' . $d7 . '.field_data_commerce_stock
        WHERE bundle = :bundle';
    $results = $db->query($query, [':bundle' => 'samhsa_publications']);
    $values = [];
    foreach ($results as $record) {
      $values[] = $record->entity_id;
    }
    $ids = implode(",", $values);
    // Switch back.
    Database::setActiveConnection();
    foreach ($values as $key => $value) {
      // Delete stock.
      $query = \Drupal::database()->delete('commerce_stock_transaction');
      $query->condition('entity_id', $value, '=');
      $query->execute();
      // $results = $db->delete($query, array(':id' => $key));
    }

    $this->output()->writeln(count($values) . ' commerce stock transactions records were rolled back.');
  }

}
