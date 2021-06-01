<?php

/**
 * @file
 * Contains \Drupal\jbs_commerce_import_product_data\ImportProductDataFunctionsInterface.
 */

namespace Drupal\jbs_commerce_import_product_data;


interface ImportProductDataFunctionsInterface {

  /**
   * importData() gets all products from a .csv and sets fields based on the
   * column values.
   *
   * @return mixed
   */
  public function importData();
}
