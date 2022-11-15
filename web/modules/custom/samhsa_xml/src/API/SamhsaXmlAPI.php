<?php
/**
 * @file
 * Contains \Drupal\samhsa_xml\API\SamhsaXmlAPI
 */

namespace Drupal\samhsa_xml\API;


use DOMAttr;
use DOMDocument;
use Drupal\commerce_order\Entity\Order;
use Drupal\node\Entity\Node;

class SamhsaXmlAPI {

  public function __construct() {
  }

  /**
   *
   */
  public static function loadOrderIds($date) {
    $dateParts = explode('-', $date);
    $startTime = mktime('00', '00', '00', $dateParts[1], $dateParts[2], $dateParts[0]);
    $endTime = mktime('23', '59', '59', $dateParts[1], $dateParts[2], $dateParts[0]);
    $connection = \Drupal::database();
    $query = $connection->select('commerce_order', 'ca');
    $query->condition('ca.created', $startTime, '>');
    $query->condition('ca.created', $endTime, '<');
//    $query->condition('ca.uid', 1, '>');
    //$query->condition('ca.checkout_step', 'completed');
//    $query->range(0,1);
    $query->fields('ca', ['order_id']);

    $orders = $query->execute()->fetchAllAssoc('order_id');
    return $orders;
  }

  public static function generateXML($date) {
    $addend = 8017265; // This get's added to the Drupal order ID to generate the GPO order ID.
    $ordersExported = 0;
    $orders = SamhsaXmlAPI::loadOrderIds($date);
    $dom = new DOMDocument();
    $dom->encoding = 'utf-8';
    $dom->xmlVersion = '1.0';
    $dom->formatOutput = TRUE;
    $xml_file_name = '/tmp/orders_temp.xml'; //You can give your path to save file.
    $unprocessedOrders = 0;

    $root = $dom->createElement('CpCpl');
    foreach ($orders as $order_id) {
      $skip = FALSE;
      // Gather the data for the order.
      $order = Order::load($order_id->order_id);
      $customer = $order->getCustomer();
      $profile = $order->getBillingProfile();
      if ($profile && $profile->hasField('address') && isset($profile->get('address')
            ->getValue()[0])) {
        $address = $profile->get('address')->getValue()[0];
      }
      else {
        $skip = TRUE;
        $unprocessedOrders = $unprocessedOrders + 1;
        // Log this order
        \Drupal::logger('samhsa_xml')->alert('The Order @order_id was not processed into the XML because it has no mailing address.',
          [
            '@order_id' => $order_id->order_id,
          ]
        );
      }

      if (!$skip) {
//        dsm($order_id);
        $orderCode = (int) $order->id() + (int) $addend;

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
        $email = '';
        if ($profile && $profile->hasField('field_phone_number') && isset($profile->get('field_phone_number')
              ->getValue()[0]['value'])) {
          $phone = $profile->get('field_phone_number')->getValue()[0]['value'];
        }
        if ($profile && $profile->hasField('field_phone_extension') && isset($profile->get('field_phone_extension')
              ->getValue()[0]['value'])) {
          $ext = ' ext:' . $profile->get('field_phone_extension')->getValue()[0]['value'];
        }
        $fullPhone = $phone . $ext;
        if ($customer) {
          $email = $customer->getEmail();
        }

        $orderDateTime = date('c', $order->getPlacedTime());

        // Build the XML for the order.
        $order_node = $dom->createElement('Cp');

        $child = $dom->createElement('ORDERCODE', $orderCode);
        $order_node->appendChild($child);

        $child = $dom->createElement('LOGINID', '');
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

        $child = $dom->createElement('CCPROCDATE', $orderDateTime);
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

        $child = $dom->createElement('ORDERTYPE', '');
        $order_node->appendChild($child);

        $child = $dom->createElement('CALLDATETIME', $orderDateTime);
        $order_node->appendChild($child);

        $child = $dom->createElement('AGENTID', '');
        $order_node->appendChild($child);

        $root->appendChild($order_node);

        $items = $order->getItems();
        foreach ($items as $item) {
          //        dsm($item);
          $GpoPubNumber = '-- NOT AVAILABLE --';
          if ($item->hasField('field_gpo_pubcode') && isset($item->get('field_gpo_pubcode')
                ->getValue()[0]['value'])) {
            $GpoPubNumber = $item->get('field_gpo_pubcode')->getValue()[0]['value'];
          }
          $GpoPubNumber = SamhsaXmlAPI::getGpoNumber($item);
          $quantity = $item->getQuantity();

          $item_node = $dom->createElement('Cpl');

          $child = $dom->createElement('ORDERCODE', $orderCode);
          $item_node->appendChild($child);

          $child = $dom->createElement('SOURCE', '');
          $item_node->appendChild($child);

          $child = $dom->createElement('SEQ', '1');
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
      'field_date' => $date,
      'field_xml_upload' => [
        'target_id' => $file->id(),
        'display' => TRUE,
      ],
    ]);
    $newXmlNode = $node->save();
//    if ($newXmlNode) {
//      foreach ($orders as $order_id) {
//        $order = Order::load($order_id->order_id);
//        $order->getState()->applyTransitionById('process');
//        $order->getState()->applyTransitionById('complete');
//        $order->save();
//      }
//    }

    $messenger = \Drupal::messenger();
    $messenger->addStatus(t('@count Orders exported to XML', ['@count' => $ordersExported]));
    if ($unprocessedOrders) {
      $messenger->addStatus(t('@count Orders were not processed because they lack a mailing address.', ['@count' => $unprocessedOrders]));
    }

  }

  public static function getGpoNumber($item) {
    if ($item->hasPurchasedEntity()) {
      $purchasedItemId = $item->getPurchasedEntityId();
      $connection = \Drupal::database();

      // Strategy 1: accessing data from a new field added to the Variant
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

  public static function testForExport($date) {
    $connection = \Drupal::database();
    $query = $connection->select('node__field_date', 'date');
    $query->condition('date.field_date_value', $date);
    return $query->countQuery()->execute()->fetchField();
  }

}
