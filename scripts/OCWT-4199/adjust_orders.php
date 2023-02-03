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
$date_range_end = '2023-02-02 00:00:00';

// These orders were created by admins and are allowed to exceed our max quantities
// so these will be done by hand
$order_id_excludes = '117702,114614,103307,98386,98061,93195,92781,87584';
$order_limits_file = 'product_order_limits.csv';

// Pull in what we know about product packaging and item order max
$fh = fopen($order_limits_file, "r");

while (($data = fgetcsv($fh, 1000, ",")) !== FALSE) {
  $limit_data[$data[0]] = [
    'product' => $data[1],
    'package_qty' => $data[2],
    'max_packages' => $data[3],
  ];
}

// Above should be working, below is not working

// Run query to gather all orders we want to possibly correct
$database = \Drupal::database();

$sql = <<<EOD
  SELECT co.order_item_id, pv.sku, co.quantity
  FROM commerce_order_item co
  JOIN commerce_product_variation_field_data pv ON co.purchased_entity = pv.variation_id
  WHERE co.order_item_id NOT IN (':order_id_excludes')
  AND co.changed > UNIX_TIMESTAMP(':date_range_begin')
  AND co.changed < UNIX_TIMESTAMP(':date_range_end')
  limit 5
EOD;

$results = $database->query($sql, [
  ':order_id_excludes' => $order_id_excludes,
  ':date_range_begin' => $date_range_begin,
  ':date_range_end' => $date_range_end,
]);

dpm($results);
