<?php
/**
 * OCWT-4136 - Due to negligence and incompetence on the part of store
 * fulfillment, we need to adjust some order quantities to account for shipment
 * of large packages of items rather than individual items.
 *
 * This script is to be run via drush: drush php:script ./adjust_orders.php
 *
 * To capture the output as a log file, run: drush:
 *   drush php:script ./adjust_orders.php | tee adjustments.tsv
 *
 */

// This is our list of many skus that we want to modify orders for.
//include(__DIR__ . '/target_skus.inc');

$modified_orders = 0;
$total_orders = 0;

// Date range of orders we will modify
$date_range_begin = '2022-09-29 00:00:00';
$date_range_end = '2023-04-05 00:00:00';

// Run query to gather all orders we want to possibly correct
$database = \Drupal::database();

// To do small batches for testing purposes, just add a 'LIMIT 20' to this query
// BUG: We have to embed the SKUs directly here because using :placeholder does not work.
$sql = <<<EOD
  SELECT co.order_item_id, co.order_id, pv.sku, co.quantity
  FROM commerce_order_item co
  JOIN commerce_product_variation_field_data pv ON co.purchased_entity = pv.variation_id
  WHERE pv.sku IN (
    'PEP18-01',
    'PEP18-02',
    'PEP18-03',
    'PEP18-04',
    'SMA16-4952SPANISH',
    'PEP19-12',
    'PEP19-08',
    'PEP19-06',
    'PEP19-07',
    'PEP20-03-03-003',
    'PEP20-03-03-004',
    'SMA03-3824',
    'SMA14-4873SPANISH',
    'SMA15-3601',
    'SMA15-3605',
    'SMA15-4024',
    'SMA15-4127',
    'SMA15-4731',
    'SMA16-4956',
    'SMA18-5070',
    'SMA18-5070EXSUMM',
    'SMA15-4923SP',
    'SMA16-4923',
    'PEP20-03-01-005',
    'PEP20-03-01-002',
    'PEP20-03-01-004',
    'PEP20-03-01-075',
    'PEP20-03-01-009',
    'PEP20-03-01-073',
    'PEP20-03-01-017',
    'PEP20-03-01-021',
    'PEP20-03-01-070',
    'PEP20-03-01-025',
    'PEP20-03-01-029',
    'PEP20-03-01-061',
    'PEP20-03-01-033',
    'PEP20-03-01-037',
    'PEP20-03-01-041',
    'PEP20-03-01-045',
    'PEP20-03-01-049',
    'PEP20-03-01-053',
    'PEP20-03-01-057',
    'ADM90-0537',
    'SMA10-4120',
    'SMA14-4869SPANISH',
    'SMA14-4870',
    'SMA14-4870SPANISH',
    'SMA14-4871SPANISH',
    'SMA14-4872SPANISH',
    'SMA14-4874SPANISH',
    'SMA14-4885SPANISH',
    'SMA14-4893SPANISH',
    'PEP19-03-01-001',
    'SMA15-4153'
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

  $sku = $result->sku;
  $quantity_ordered = $result->quantity;
  $order_item_id = $result->order_item_id;
  $order_id = $result->order_id;

  if ($quantity_ordered > 25) {
    $revised_quantity_ordered = 25;
  }
  else {
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

  print "Order ID: $order_id\tSKU: $sku\tOriginal quantity: $quantity_ordered\tNew quantity: $revised_quantity_ordered\n";

}

print "\n\nFinished\n\nReviewed $total_orders order items and modified $modified_orders\n\n";

// Spits out a drush warning otherwise
return 0;
