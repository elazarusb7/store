<?php
/**
 * @file
 * Contains \Drupal\samhsa_testbed\SamhsaTestbed\SamhsaTestbedController
 */

namespace Drupal\samhsa_testbed\SamhsaTestbed;

use Drupal\commerce_order\Entity\Order;
use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;
use Drupal\samhsa_gpo\API\SamhsaGpoAPI;

class SamhsaTestbedController extends ControllerBase {
  public function __construct() {}

  public function testbed() {
    // Test code here
    $purchasedItemId = 6017;

    $connection = \Drupal::database();

    // Strategy 1: accessing data from a new field added to the Variant
    // Keeping this around because working from the Variant is the correct way to use Commerce
    // Maybe at some point we'll adopt that approach.
          $query = $connection->select('commerce_product_variation_field_data', 'pep');
          $query->condition('pep.variation_id', $purchasedItemId);
          $query->fields('pep', ['sku']);

          $pepNumber = $query->execute()->fetchAll()[0]->sku;
//          $result = $gpoPubcode[0]->field_gpo_pubcode_value;

          dsm($pepNumber);

    // Strategy 2: Accessing data from the field in the main product Entity
    // Sub-query: return Product entity ID from Variation ID
//    $subQuery = $connection->select('commerce_product__variations', 'variations');
//    $subQuery->condition('variations.variations_target_id', $purchasedItemId);
//    $subQuery->fields('variations', ['entity_id']);
//
//    // Main query: return GPO number from Entity ID retrieved in sub-query.
//    $query = $connection->select('commerce_product__field_gpo_pubcode', 'product_field');
//    $query->condition('product_field.entity_id', $subQuery);
//    $query->fields('product_field', ['field_gpo_pubcode_value']);
//
//    $result = $query->execute()->fetchAll();
//    return $result[0]->field_gpo_pubcode_value;

    $output = 'DEFAULT TEST OUTPUT';
    return array(
      '#markup' => $output,
    );
  }
}
