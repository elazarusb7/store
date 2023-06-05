<?php
/**
 * OCWT-5645 - Identify multiple orders from the same email address and merge
 * them into one order to simplify fulfillment. Does not respect max order limits.
 *
 * This script is to be run via drush: drush php:script ./merge_orders.php
 *
 * To capture the output as a log file, run: drush: drush php:script
 * ./adjust_orders.php | tee adjustments.tsv
 *
 */

$logfile_contents = ['Order', 'Action', 'Description'];

// Date range of orders we will modify
$date_range_begin = '2022-09-29 00:00:00';
// End date is for testing and possibly batching. It's not required for the task itself.
$date_range_end = '2023-10-29 00:00:00';

// Run query to gather all orders we want to possibly correct
$database = \Drupal::database();

//
// STEP: Select all relevant orders and corresponding order items
//

$sql = <<<EOD
  SELECT co.mail AS order_mail,  co.order_id, FROM_UNIXTIME(co.created)
    AS order_created, co.state AS order_state, coi.*
  FROM commerce_order_item coi
  JOIN commerce_order co ON coi.order_id = co.order_id
  WHERE co.created > UNIX_TIMESTAMP(:date_range_begin)
  AND co.created < UNIX_TIMESTAMP(:date_range_end)
  AND co.state IN ('pending')
  ORDER BY co.mail ASC, co.order_id ASC
  limit 100
EOD;

$query = $database->query($sql, [
  ':date_range_begin' => $date_range_begin,
  ':date_range_end' => $date_range_end,
]);

$results      = $query->fetchAll();
$total_orders = count($results);

// Reorder order data based on user email
foreach ($results as $result) {
  $user_orders[$result->order_mail][$result->order_id][] = $result;
}

//
// STEP: Prune single orders out of our data set
//

foreach ($user_orders as $orders_key => $values) {
  if (count($values) < 2) {
    unset($user_orders[$orders_key]);
  }
}

if (count($user_orders) === 0) {
  exit("No duplicate orders found, exiting.\n");
}

//
// STEP: Do the actual order merging and merged order cancellation
//

foreach ($user_orders as $orders_key => $values) {

  // Determine order to merge to and orders to cancel

  $all_order_ids = array_keys($values);

  // Order IDs are ordered ASC, so we can just shift it.
  $merged_order_id  = array_shift($all_order_ids);
  $orders_to_cancel = $all_order_ids;
  unset($all_order_ids);

  $logfile_contents[] = ['Info', "Merging the items from these orders: " . implode(",", $orders_to_cancel) . "...\nInto
  order id $merged_order_id\n"];

  $mergeable_order_data = _merge_user_orders($values, $merged_order_id);

  _update_merged_order_items($mergeable_order_data, $merged_order_id);
  _cancel_orders($orders_to_cancel);
}

/*
 * Takes order items nested in multiple orders and merges then into one order
 * with quantities summed.
 */
function _merge_user_orders(array $order_data, int $merged_order_id): array {
  global $logfile_contents;

  $mergeable_order_data = [];
  $normalized_orders    = [];

  // Naturally, our data is a mess. We need to unnest it.
  foreach ($order_data as $parent => $children) {
    foreach ($children as $child) {
      $normalized_orders[] = $child;
    }
  }

  foreach ($normalized_orders as $order_values) {
    if (isset($mergeable_order_data[$order_values->purchased_entity])) {
      $mergeable_order_data[$order_values->purchased_entity]->quantity += $order_values->quantity;
      $logfile_contents[] = ['Order item merged', "Order ID: $merged_order_id\nProduct ID: $order_values->purchased_entity\nQuantity: $order_values->quantity\n"];
    }
    else {
      $mergeable_order_data[$order_values->purchased_entity] = $order_values;
      $logfile_contents[] = ['Order item add', "Order ID: $merged_order_id\nProduct ID: $order_values->purchased_entity\nQuantity: $order_values->quantity\n"];
    }
  }

  return $mergeable_order_data;
}

// Our poor man's logging
$i = 0;

$fp = fopen(__DIR__ . '/merge_orders_' . getmypid() . '.csv', "w");
foreach ($logfile_contents as $fields) {
  // Push a counter at the beginning
  $i++;
  array_unshift($i, $fields);
  fputcsv($fp, $fields);
}
fclose($fp);

/**
 * Takes the cleaned up order data that has been merged and updates the
 * corresponding rows in the commerce_order_item table
 */
function _update_merged_order_items(array $order_data, int $merged_order_id): void {
  global $logfile_contents;


  // Create a new uuid so new entries have one
  $uuid_service = \Drupal::service('uuid');

  $order_item_sql = <<<EOD
    REPLACE INTO commerce_order_item 
    SET
     `order_item_id` = :order_item_id,
     `type` = :type,
     `uuid` = :uuid,
     `order_id` = :order_id,
     `purchased_entity` = :purchased_entity,
     `title` = :title,
     `quantity` = :quantity,
     `unit_price__number` = :unit_price__number,
     `unit_price__currency_code` = :unit_price__currency_code,
     `overridden_unit_price` = :overridden_unit_price,
     `total_price__number` = :total_price__number,
     `total_price__currency_code` = :total_price__currency_code,
     `uses_legacy_adjustments` = :uses_legacy_adjustments,
     `data` = :data,
     `created` = :created,
     `changed` = UNIX_TIMESTAMP(),
     `locked` = :locked
EOD;

  foreach ($order_data as $product_id => $values) {
    $database = \Drupal::database();
    $uuid     = $uuid_service->generate();

    $query = $database->query($order_item_sql, [
      ':order_item_id' => $values->order_item_id,
      ':type' => $values->type,
      ':uuid' => $uuid,
      ':order_id' => $merged_order_id,
      ':purchased_entity' => $values->purchased_entity,
      ':title' => $values->title,
      ':quantity' => $values->quantity,
      ':unit_price__number' => $values->unit_price__number,
      ':unit_price__currency_code' => $values->unit_price__currency_code,
      ':overridden_unit_price' => $values->overridden_unit_price,
      ':total_price__number' => $values->total_price__number,
      ':total_price__currency_code' => $values->total_price__currency_code,
      ':uses_legacy_adjustments' => $values->uses_legacy_adjustments,
      ':data' => $values->data,
      ':created' => $values->created,
      ':locked' => $values->locked
    ]);

    $logfile_contents[] = ['Merging product into order', "Order ID: $merged_order_id\nProduct ID: $values->purchased_entity\nQuantity: $values->quantity\n"];

  }
}

function _cancel_orders(array $order_ids): void {
  global $logfile_contents, $uuid_service;

  $cancel_order_sql = <<<EOD
    UPDATE commerce_order
    SET state = 'canceled'
    WHERE order_number = :order_number
EOD;

  foreach ($order_ids as $order_id) {
    $logfile_contents[] = ['Order cancellation', "Order #$order_id 'canceled'\n"];

    $database = \Drupal::database();

    $database->query($cancel_order_sql, [
      ':order_number' => $order_id
    ]);
  }

}
