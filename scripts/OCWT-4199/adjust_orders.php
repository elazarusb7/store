<?php
/**
 * OCWT-4136 - Due to negligence and incompetence on the part of store
 * fulfillment, we need to adjust some order quantities to account for shipment
 * of large packages of items rather than individual items.
 *
 * This script is to be run via drush: drush php:script ./adjust_orders.php
 *
 */

// Date range of orders we will modify
$date_range_begin = '2022-09-10 00:00:00';
$date_range_end = '2023-02-03 00:00:00';

// These orders were created by admins and are allowed to exceed our max quantities
// so these will be done by hand
$order_id_excludes = '117702,114614,103307,98386,98061,93195,92781,87584';
$order_limits_file = __DIR__ . '/product_order_limits.csv';

// Pull in what we know about product packaging and item order max
$fh = fopen($order_limits_file, "r");

while (($data = fgetcsv($fh)) !== FALSE) {
  $limit_data[$data[1]] = [
    'package_qty' => $data[2],
    'max_packages' => $data[3],
  ];
}

// Above should be working, below is not working

// Run query to gather all orders we want to possibly correct
$database = \Drupal::database();

// To do small batches for testing purposes, just add a 'LIMIT 20' to this query
$sql = <<<EOD
  SELECT co.order_item_id, co.order_id, pv.sku, co.quantity
  FROM commerce_order_item co
  JOIN commerce_product_variation_field_data pv ON co.purchased_entity = pv.variation_id
  WHERE co.order_item_id NOT IN (:order_id_excludes)
  AND co.changed > UNIX_TIMESTAMP(:date_range_begin)
  AND co.changed < UNIX_TIMESTAMP(:date_range_end)
EOD;

$query = $database->query($sql, [
  ':order_id_excludes' => $order_id_excludes,
  ':date_range_begin' => $date_range_begin,
  ':date_range_end' => $date_range_end,
]);

$results = $query->fetchAll();

// Check each individual order item for excessive items
foreach ($results as $result) {

  $sku = $result->sku;

  // We only update SKUs that are in our $order_limits_file
  if (!isset($limit_data[$sku])) {
    continue;
  }

  $quantity_ordered = $result->quantity;
  $order_item_id = $result->order_item_id;
  $order_id = $result->order_id;

  $product_package_quantity = $limit_data[$sku]['package_qty'];
  $product_max_packages = $limit_data[$sku]['max_packages'];
  $max_order_items_allowed = $product_package_quantity * $product_max_packages;

  // Convert single item orders to packages order
  $items_to_packages = ceil($quantity_ordered / $product_package_quantity);

  // If the order quantity is too high, reduce it to max allowed
  if ($items_to_packages >= $product_max_packages) {
    $revised_quantity_ordered = $product_max_packages;
  } else {
    $revised_quantity_ordered = $items_to_packages;
  }

  // Set order item to  $revised_quantity_ordered
  $sql = <<<EOD
    UPDATE commerce_order_item
    SET quantity = :revised_quantity_ordered,
        changed = UNIX_TIMESTAMP()
    WHERE order_item_id = :order_item_id
EOD;

  $database->query($sql, [
    ':revised_quantity_ordered' => $revised_quantity_ordered,
    ':order_item_id' => $order_item_id,
  ]);

  // TODO: some sort of logging here
  print "Order ID: $order_id SKU: $sku Original quantity: $quantity_ordered New quantity: $revised_quantity_ordered\n";

}
