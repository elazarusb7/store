<?php
/**
 * OCWT-5645 - Identify multiple orders from the same email address and merge
 * them into one order to simplify fulfillment.
 *
 * This script is to be run via drush: drush php:script ./merge_orders.php
 *
 * To capture the output as a log file, run: drush: drush php:script
 * ./adjust_orders.php | tee adjustments.tsv
 *
 */

// Date range of orders we will modify
$date_range_begin = '2022-09-29 00:00:00';
$user_orders = [];

// Run query to gather all orders we want to possibly correct
$database = \Drupal::database();

/**
 * Select all relevant orders and corresponding order items
 */
$sql = <<<EOD
  SELECT co.mail,  co.order_id, FROM_UNIXTIME(co.created), co.state, coi.order_item_id,
    coi.purchased_entity, FLOOR(coi.quantity)
  FROM commerce_order_item coi
  JOIN commerce_order co ON coi.order_id = co.order_id
  WHERE co.created > UNIX_TIMESTAMP(:date_range_begin)
  AND co.state IN ('pending')
  ORDER BY co.mail, co.order_id ASC
  -- Debug
  limit 100;
EOD;

$query = $database->query($sql, [
  ':date_range_begin' => $date_range_begin,
]);

$results = $query->fetchAll();
$total_orders = count($results);

print "DEBUG: Got $total_orders results\n";

foreach ($results as $result) {

  $user_orders[$result->mail][$result->order_id][] = $result;
//  $quantity_ordered = $result->quantity;
//  $order_item_id = $result->order_item_id;
//  $order_id = $result->order_id;

}



/**
 * Prune single orders out of our data set
 */
foreach ($user_orders as $orders_key => $values) {

  if (count($values) < 2) {
    unset($user_orders[$orders_key]);
  }
}



return;





/**
 *
 */


/**
 *
 */


/**
 *
 */


/**
 *
 */


/**
 *
 */







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
