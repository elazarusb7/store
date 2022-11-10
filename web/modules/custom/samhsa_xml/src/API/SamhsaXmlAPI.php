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
    $query->condition('ca.uid', 1, '>');
    //$query->condition('ca.checkout_step', 'completed');
    $query->range(0,1);
    $query->fields('ca', ['order_id']);

    $orders = $query->execute()->fetchAllAssoc('order_id');
    return $orders;
  }

  public static function generateXML($date, $addend) {
    $ordersExported = 0;
    $orders = SamhsaXmlAPI::loadOrderIds($date);
    $dom = new DOMDocument();
    $dom->encoding = 'utf-8';
    $dom->xmlVersion = '1.0';
    $dom->formatOutput = TRUE;
    $xml_file_name = '/tmp/orders_temp.xml'; //You can give your path to save file.

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
      }

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
        // $GpoPubNumber = isset($item->get('field_gpo_pubcode')->getValue()[0]['value']) ? $item->get('field_gpo_pubcode')->getValue()[0]['value'] : '';
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
    $node->save();

    $messenger = \Drupal::messenger();
    $messenger->addStatus(t('@count Orders exported to XML', ['@count' => $ordersExported]));

  }

  public static function testForExport($date) {
    $connection = \Drupal::database();
    $query = $connection->select('node__field_date', 'date');
    $query->condition('date.field_date_value', $date);
    return $query->countQuery()->execute()->fetchField();
  }

}
