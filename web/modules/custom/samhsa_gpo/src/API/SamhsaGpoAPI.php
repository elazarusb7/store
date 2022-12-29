<?php
/**
 * @file
 * Contains \Drupal\samhsa_gpo\API\SamhsaGpoAPI
 */

namespace Drupal\samhsa_gpo\API;

use DOMDocument;
use Drupal\commerce_order\Entity\Order;
use Drupal\node\Entity\Node;

class SamhsaGpoAPI {

  // NEVER CHANGE THE VALUE OF THIS PROPERTY!
  private static $addend = 8017265; //This is added to the Drupal order ID to generate the GPO order ID.

  public function __construct() {}

  /**
   *
   * Get all the drupal order id for a given day.
   * @param $date (YYYY-MM-DD)
   *
   * @return mixed
   */
  public static function loadOrderIds($date) {
    $dateParts = explode('-', $date);
    $startTime = mktime('00', '00', '00', $dateParts[1], $dateParts[2], $dateParts[0]);
    $endTime = mktime('23', '59', '59', $dateParts[1], $dateParts[2], $dateParts[0]);
    $connection = \Drupal::database();
    $query = $connection->select('commerce_order', 'ca');
    $query->condition('ca.created', $startTime, '>=');
    $query->condition('ca.created', $endTime, '<=');
    $query->fields('ca', ['order_id']);

    return $query->execute()->fetchAllAssoc('order_id');;
  }

  // Based on technique explained here. https://stackoverflow.com/questions/486757/how-to-generate-xml-file-dynamically-using-php

  /**
   * Generate the XML output
   * @param $date (YYYY-MM-DD)
   *
   * @return void
   * @throws \DOMException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function generateXML($date) {
    $ordersExported = 0;
    $orders = SamhsaGpoAPI::loadOrderIds($date);
    $dom = new DOMDocument();
    $dom->encoding = 'iso-8859-1';
    $dom->xmlVersion = '1.0';
    $dom->formatOutput = TRUE;
    $xml_file_name = '/tmp/orders_temp.xml'; // You can give your path to save file.
    $unprocessedOrders = 0;

    $root = $dom->createElement('CpCpl');
    foreach ($orders as $order_id) {
      $skip = FALSE;
      // Gather the data for the order.
      $order = Order::load($order_id->order_id);

      // If the order has been canceled, do not process the order.
      $currentState = $order->getState()->getId();
      if ($currentState === 'cancel' || $currentState === 'draft') {
        $skip = TRUE;
        $unprocessedOrders = $unprocessedOrders + 1;
        // Log this skipped order
        \Drupal::logger('samhsa_gpo')->alert('The Order @order_id was not processed into the XML because it has a status of @status.',
          [
            '@order_id' => $order_id->order_id,
            '@status' => $currentState,
          ]
        );
      }
      else {
//        $customer = $order->getCustomer();
        $profile = $order->getBillingProfile();

        // If the order does not have an address, do not process the order.
        if ($profile && $profile->hasField('address') && isset($profile->get('address')
              ->getValue()[0])) {
          $address = $profile->get('address')->getValue()[0];
        }
        else {
          $skip = TRUE;
          $unprocessedOrders = $unprocessedOrders + 1;
          // Log this skipped order
          \Drupal::logger('samhsa_gpo')->alert('The Order @order_id was not processed into the XML because it has no mailing address.',
            [
              '@order_id' => $order_id->order_id,
            ]
          );
        }
      }

      // Build the XML inside the XML root created above.
      if (!$skip) {
        // First, gather all the required data
        // The GPO compliant order code is generated by adding a fixed number (addend) to Drupal's order number.
        $orderCode = (int) $order->id() + self::$addend;

        $fName = $address['given_name'];
        $lName = $address['family_name'];
        $suffix = $address['additional_name'];
        $company = $address['organization'];
        $street1 = $address['address_line1'];
        $street2 = $address['address_line2'];
        $city = $address['locality'];
        $state = $address['administrative_area'];
        $zip = $address['postal_code'];
        $country = $address['country_code'];

        $phone = '';
        $ext = '';
        $email = SamhsaGpoAPI::getOrderEmail($order_id->order_id);
        if ($profile && $profile->hasField('field_phone_number') && isset($profile->get('field_phone_number')
              ->getValue()[0]['value'])) {
          $phone = $profile->get('field_phone_number')->getValue()[0]['value'];
        }
        if ($profile && $profile->hasField('field_phone_extension') && isset($profile->get('field_phone_extension')
              ->getValue()[0]['value'])) {
          $ext = ' ext:' . $profile->get('field_phone_extension')->getValue()[0]['value'];
        }
        $fullPhone = $phone . $ext;

        // We force the format to midnight in spite of when the order was placed because that is what the GPO XML requires
        $orderDateTime = date('Y-m-d', $order->getPlacedTime()) . 'T00:00:00+00:00';

        // Build the XML for the order.
        // This first part is the information about the order.
        $order_node = $dom->createElement('Cp');

        $child = $dom->createElement('ORDERCODE', $orderCode);
        $order_node->appendChild($child);

        $child = $dom->createElement('LOGINID', $email);
        $order_node->appendChild($child);

        $child = $dom->createElement('CUSTOMERNAME', 'SAMHSA');
        $order_node->appendChild($child);

        $child = $dom->createElement('ITEMCATEGORY', '31');
        $order_node->appendChild($child);

        $child = $dom->createElement('OPERID', 'SAMHSA');
        $order_node->appendChild($child);

        $child = $dom->createElement('ORDERORIGINATION', 'SAMHSA');
        $order_node->appendChild($child);

        $child = $dom->createElement('ORDERSTATUS', 'SAMHSANEW');
        $order_node->appendChild($child);

        $child = $dom->createElement('CUSTOMERTYPECODE', '31');
        $order_node->appendChild($child);

        $child = $dom->createElement('FIRST', $fName);
        $order_node->appendChild($child);

        $child = $dom->createElement('LAST', $lName);
        $order_node->appendChild($child);

        $child = $dom->createElement('SUFFIX', $suffix);
        $order_node->appendChild($child);

        $child = $dom->createElement('COMPANY', $company);
        $order_node->appendChild($child);

        $child = $dom->createElement('STREET1', $street1);
        $order_node->appendChild($child);

        $child = $dom->createElement('STREET2', $street2);
        $order_node->appendChild($child);

        $child = $dom->createElement('CITY', $city);
        $order_node->appendChild($child);

        $child = $dom->createElement('ST', $state);
        $order_node->appendChild($child);

        $child = $dom->createElement('ZIP', $zip);
        $order_node->appendChild($child);

        $child = $dom->createElement('COUNTRY', $country);
        $order_node->appendChild($child);

        $child = $dom->createElement('PHONE', $fullPhone);
        $order_node->appendChild($child);

        $child = $dom->createElement('EMAIL', $email);
        $order_node->appendChild($child);

        $child = $dom->createElement('CCARD', '');
        $order_node->appendChild($child);

        $child = $dom->createElement('CCNAME', '');
        $order_node->appendChild($child);

        $child = $dom->createElement('CCAPPROVALCODE', '');
        $order_node->appendChild($child);

        $child = $dom->createElement('CCPROCDATE', '');
        $order_node->appendChild($child);

        $child = $dom->createElement('CCTOTAL', '0.00');
        $order_node->appendChild($child);

        $child = $dom->createElement('CCPAYSTATUS', '');
        $order_node->appendChild($child);

        $child = $dom->createElement('CARDEXP', '');
        $order_node->appendChild($child);

        $child = $dom->createElement('CARDTYPE', '');
        $order_node->appendChild($child);

        $child = $dom->createElement('NOTES', '');
        $order_node->appendChild($child);

        $child = $dom->createElement('ORDERTOT', '0.00');
        $order_node->appendChild($child);

        $child = $dom->createElement('SALESTOT', '0.00');
        $order_node->appendChild($child);

        $child = $dom->createElement('SERVICEFEE', '0.00');
        $order_node->appendChild($child);

        $child = $dom->createElement('PRIORITY', '0');
        $order_node->appendChild($child);

        $child = $dom->createElement('INTLFEE', '0');
        $order_node->appendChild($child);

        $child = $dom->createElement('ORDERTYPE', 'X');
        $order_node->appendChild($child);

        $child = $dom->createElement('CALLDATETIME', $orderDateTime);
        $order_node->appendChild($child);

        $child = $dom->createElement('AGENTID', '');
        $order_node->appendChild($child);

        $root->appendChild($order_node);

        // Now build the XML nodes for the ordered items.
        $itemSeq = 1;
        $items = $order->getItems();
        foreach ($items as $item) {
          $GpoPubNumber = SamhsaGpoAPI::getGpoNumber($item);
          $quantity = floor($item->getQuantity());

          $item_node = $dom->createElement('Cpl');

          $child = $dom->createElement('ORDERCODE', $orderCode);
          $item_node->appendChild($child);

          $child = $dom->createElement('SEQ', $itemSeq);
          $item_node->appendChild($child);

          $child = $dom->createElement('SOURCE', '');
          $item_node->appendChild($child);

          $child = $dom->createElement('PUBCODE', $GpoPubNumber);
          $item_node->appendChild($child);

          $child = $dom->createElement('QUANTITY', $quantity);
          $item_node->appendChild($child);

          $child = $dom->createElement('PRICEA', '0.00');
          $item_node->appendChild($child);

          $child = $dom->createElement('DISCOUNT', '0');
          $item_node->appendChild($child);

          $child = $dom->createElement('TOTPRICE', '0.00');
          $item_node->appendChild($child);

          $root->appendChild($item_node);

          $itemSeq++;
        }
        $ordersExported = $ordersExported + 1;
      }

    }
    $dom->appendChild($root);
    $dom->save($xml_file_name);

    // Create file object from remote URL.
    $data = file_get_contents('/tmp/orders_temp.xml');
    $file = \Drupal::service('file.repository')
      ->writeData($data, 'private://gpo-xml/orders--' . $date . '.xml');

    // Create node object with attached file.
    $node = Node::create([
      'type' => 'gpo_xml_upload',
      'title' => 'Orders Export: ' . $date,
      'field_date_of_orders_in_upload' => $date,
      'field_xml_upload' => [
        'target_id' => $file->id(),
        'display' => TRUE,
      ],
    ]);
    $newXmlNode = $node->save();

    // If the xml file has been created and saved without error.
    // Loop through the orders and change their status to 'process'
    // Which the customized system also recognized as "pick_slips_generated"
    if ($newXmlNode) {
      foreach ($orders as $order_id) {
        $order = Order::load($order_id->order_id);
        $currentState = $order->getState()->getId();
        if ($currentState === 'pending') {
          $order->getState()->applyTransitionById('process');
//          $order->getState()->applyTransitionById('complete');
        }
//        else if ($currentState === 'process' || $currentState === 'pick_slips_generated') {
//          $order->getState()->applyTransitionById('complete');
//        }
        $order->save();
      }
    }

    $messenger = \Drupal::messenger();
    $messenger->addStatus(t('@count Orders for @date exported to XML', ['@count' => $ordersExported, '@date' => $date]));
    \Drupal::logger('samhsa_gpo')->info('@count Orders for @date exported to XML.',
      [
        '@count' => $ordersExported,
        '@date' => $date
      ]
    );
    if ($unprocessedOrders) {
      $messenger->addStatus(t('@count Orders for @date were not processed because they were not compliant with the export requirements.', ['@count' => $unprocessedOrders]));
      \Drupal::logger('samhsa_gpo')->alert('@count Orders were not processed because they were not compliant with the export requirements.',
        [
          '@count' => $unprocessedOrders,
          '@date' => $date
        ]
      );
    }

  }

  /**
   * Gets the GPO number stored in Product.
   * There are two strategies
   * 1. Pull the value from a field in the product variant (DISABLED)
   * 2. Pull the value from a field in the main product (ENABLED)
   *
   * The 2nd technique is a bit of a hack because it's best practice to put data like this into variants.
   * But hey! This is what we inherited, so just trying to make the best of it.
   * @param $item
   *
   * @return string
   */
  public static function getGpoNumber($item): string {
    if ($item->hasPurchasedEntity()) {
      $purchasedItemId = $item->getPurchasedEntityId();
      $connection = \Drupal::database();

      // Strategy 1: accessing data from a new field added to the Variant
      // Keeping this around because working from the Variant is the correct way to use Commerce
      // Maybe at some point we'll adopt that approach.
      //      $query = $connection->select('commerce_product_variation__field_gpo_pubcode', 'code');
      //      $query->condition('code.entity_id', $purchasedItemId);
      //      $query->fields('code', ['field_gpo_pubcode_value']);
      //
      //      $gpoPubcode = $query->execute()->fetchAll();
      //      return $gpoPubcode[0]->field_gpo_pubcode_value;

      // Strategy 2: Accessing data from the field in the main product Entity
      // Sub-query: return Product entity ID from Variation ID
      $subQuery = $connection->select('commerce_product__variations', 'variations');
      $subQuery->condition('variations.variations_target_id', $purchasedItemId);
      $subQuery->fields('variations', ['entity_id']);

      // Main query: return GPO number from Entity ID retrieved in sub-query.
      $query = $connection->select('commerce_product__field_gpo_pubcode', 'product_field');
      $query->condition('product_field.entity_id', $subQuery);
      $query->fields('product_field', ['field_gpo_pubcode_value']);

      $result = $query->execute()->fetchAll();
      return $result[0]->field_gpo_pubcode_value;
    }
  }

  /**
   * Tests to see if an export for a given date already exists.
   * @param $date (YYYY-MM-DD)
   *
   * @return mixed
   */
  public static function testForExport($date) {
    $connection = \Drupal::database();
    $query = $connection->select('node__field_date_of_orders_in_upload', 'date');
    $query->condition('date.field_date_of_orders_in_upload_value', $date);
    return $query->countQuery()->execute()->fetchField();
  }

  /**
   * @param $order_id
   *
   * @return mixed
   */
  public static function getOrderEmail($order_id) {
    $connection = \Drupal::database();
    $query = $connection->select('commerce_order', 'order');
    $query->condition('order.order_id', $order_id);
    $query->fields('order', ['mail']);
    return $query->execute()->fetchField();
  }

  /**
   * @param $fileObj
   *
   * @return void
   * Processes a file of GPO Order numbers (Fulfilled orders)
   * Changes the status of the matching Drupal Commerce order to "Complete"
   */
  public static function processFulfilledOrder($fileObj) {
    $API = new SamhsaGpoAPI();
    $gpoOrderNumbersArray = $API->convertFileToArray($fileObj);
    foreach($gpoOrderNumbersArray as $gpoOrderNumber) {
      // Convert the GPO order number to the matching Drupal Commerce Order ID.
      $drupalOrderId = $gpoOrderNumber - self::$addend;
      $order = Order::load($drupalOrderId);
      $currentState = $order->getState()->getId();
      if ($currentState === 'pending') {
        $order->getState()->applyTransitionById('process');
        $order->getState()->applyTransitionById('complete');
      }
      else if ($currentState === 'process' || $currentState === 'pick_slips_generated') {
        $order->getState()->applyTransitionById('complete');
      }
      $order->save();
    }
    $messenger = \Drupal::messenger();
    $messenger->addStatus(t('@count Orders were processed and set to a status of Complete.', ['@count' => count($gpoOrderNumbersArray)]));
    \Drupal::logger('samhsa_gpo')->alert('@count Orders were processed and set to a status of Complete.',
      [
        '@count' => count($gpoOrderNumbersArray)
      ]
    );
  }

  /**
   * @param $fileObj
   *
   * @return array
   *
   * Converts csv, json or xml file to an PHP array. Auto detects file type.
   */
  public function convertFileToArray($fileObj): array {
    $fullURL = \Drupal::request()->getSchemeAndHttpHost() . $fileObj->createFileUrl();
    $mimeType = $fileObj->getMimeType();
    $output = [];
    if ($mimeType === 'text/csv') {
      $f = fopen($fullURL, "r");
      while (!feof($f)) {
        $output[] = fgetcsv($f)[0];
      }
      fclose($f);
    }
    else if ($mimeType === 'application/octet-stream') {
      // JSON
      $f = file_get_contents($fullURL);
      $output = json_decode($f, true);
    }
    else if ($mimeType === 'application/xml') {
      // XML
      $f = file_get_contents($fullURL);
      $new = simplexml_load_string($f);
      $con = json_encode($new);
      $output = json_decode($con, true)['order'];
    }
    return $output;
  }

}
