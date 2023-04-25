<?php
/**
 * OCWT-4986 - Due to negligence and incompetence on the part of store
 * fulfillment, we need to adjust some order quantities to account for package
 * quantity changing from 1 to 50.
 *
 * This script is to be run via drush: drush php:script ./adjust_orders.php
 *
 * To capture the output as a log file, run: drush: drush php:script
 * ./adjust_orders.php | tee adjustments.tsv
 *
 */

$modified_orders = 0;
$total_orders = 0;

// Date range of orders we will modify
$date_range_begin = '2022-09-29 00:00:00';
$date_range_end = '2023-04-19 00:00:00';

$target_sku = "
'PEP19-02-01-001',
'SMA18-5083',
'SMA18-5081',
'SMA09-4432',
'SMA18-5091',
'PEP20-03-01-086',
'PEP20-03-01-022'
";

// Run query to gather all orders we want to possibly correct
$database = \Drupal::database();

// To do small batches for testing purposes, just add a 'LIMIT 20' to this query
$sql = <<<EOD
  SELECT co.order_item_id, co.order_id, co.quantity
  FROM commerce_order_item co
  JOIN commerce_product_variation_field_data pv ON co.purchased_entity = pv.variation_id
  WHERE pv.sku IN (
    'PEP19-02-01-001',
    'SMA18-5083',
    'SMA18-5081',
    'SMA09-4432',
    'SMA18-5091',
    'PEP20-03-01-086',
    'PEP20-03-01-022'
  )
  AND co.changed > UNIX_TIMESTAMP(:date_range_begin)
  AND co.changed < UNIX_TIMESTAMP(:date_range_end)
EOD;

$query = $database->query($sql, [
// This placeholder does not work
//  ':target_skus' => $target_skus,
  ':date_range_begin' => $date_range_begin,
  ':date_range_end' => $date_range_end,
]);

$results = $query->fetchAll();
$total_orders = count($results);

// Check each individual order item for excessive items
foreach ($results as $result) {

  $quantity_ordered = $result->quantity;
  $order_item_id = $result->order_item_id;
  $order_id = $result->order_id;

  if ($quantity_ordered > 50) {
    $revised_quantity_ordered = 2;
  }
  elseif ($quantity_ordered > 1 && $quantity_ordered <= 50) {
    $revised_quantity_ordered = 1;
  }
  else { // In case there are any 0 values, which I don't think there will be.
    continue;
  }

  $modified_orders += 1;

  // Set order item to  $revised_quantity_ordered
  $sql = <<<EOD
    UPDATE commerce_order_item
    SET quantity = :revised_quantity_ordered, changed = UNIX_TIMESTAMP()
    WHERE order_item_id = :order_item_id
EOD;

  $database->query($sql, [
    ':revised_quantity_ordered' => $revised_quantity_ordered,
    ':order_item_id' => $order_item_id,
  ]);

  // TODO: some sort of logging here
  print "Order ID: $order_id\tSKU: $target_sku\tOriginal quantity: $quantity_ordered\tNew quantity: $revised_quantity_ordered\n";

}

print "\n\nFinished\n\nReviewed $total_orders order items and modified $modified_orders\n\n";
