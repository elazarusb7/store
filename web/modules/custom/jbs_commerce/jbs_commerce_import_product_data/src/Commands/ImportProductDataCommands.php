<?php

namespace Drupal\jbs_commerce_import_product_data\Commands;

use Drupal\jbs_commerce_import_product_data\ImportProductDataFunctions;
use Drush\Commands\DrushCommands;

/**
 *
 */
class ImportProductDataCommands extends DrushCommands {
  // /**
  //   * Echos back hello with the argument provided.
  //   *
  //   * @param string $name
  //   *   Argument provided to the drush command.
  //   *
  //   * @command jbs_commerce_import_product_data:hello
  //   * @options arr An option that takes multiple values.
  //   * @options msg Whether or not an extra message should be displayed to the user.
  //   * @usage jbs_commerce_import_product_data:hello x --msg
  //   *   Display 'Hello x!' and a message.
  //   */
  //  public function hello($name, $options = ['msg' => FALSE]) {
  //    if ($options['msg']) {
  //      $this->output()->writeln('Hello ' . $name . '! This is your first Drush 9 command.');
  //    }
  //    else {
  //      $this->output()->writeln('Hello ' . $name . '!');
  //    }
  //  }

  /**
   * Drush command for importing data from the associated csv.
   *
   * @command jbs_commerce_import_product_data:import-data
   * @aliases imppd
   * @usage jbs_commerce_import_product_data:import-data
   */
  public function importData() {
    try {
      (new ImportProductDataFunctions)->importData();
      $this->output()->writeln('Import was successful.');
    }
    catch (\Exception $e) {
      $this->output()->writeln($e);
    }
  }

}
