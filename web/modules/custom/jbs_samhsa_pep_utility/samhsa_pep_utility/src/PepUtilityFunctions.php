<?php

namespace Drupal\samhsa_pep_utility;

use Drupal\commerce_shipping\Entity\Shipment;
use Drupal\commerce_order\Entity\Order;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Render\Markup;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Core\Url;
use Drupal\physical\Weight;
use Drupal\physical\WeightUnit;
use Drupal\commerce_shipping\ProposedShipment;
use Drupal\commerce_shipping\ShipmentItem;

/**
 * Class PepUtilityFunctions.
 *
 * @package Drupal\samhsa_pep_utility
 */
class PepUtilityFunctions implements PepUtilityFunctionsInterface {

  /**
   * Constructor.
   */
  public function __construct() {
    $this->basePath = getcwd();
    $request = Request::createFromGlobals();
    $this->baseUrl = $request->getBaseUrl();
  }

  /**
   * {@inheritdoc}
   */
  public function getCustomerDefaultProfileAddress($order = NULL) {
    $customer_information = NULL;
    $uid = $order->getCustomerId('uid');
    $p = \Drupal::entityTypeManager()->getStorage('profile')->loadByProperties([
      'uid' => $uid,
      'type' => 'customer',
      'is_default' => TRUE,
    ]);
    $p = array_shift($p);
    if ($p) {
      $address = array_shift($p->toArray()['address']);
      $country = \Drupal::service('country_manager')
        ->getList()[$address['country_code']]->__toString();
      $customer_information = Markup::create($address['given_name'] . '&nbsp;' . $address['family_name'] . '<br />' . $address['address_line1'] . '<br />' . $address['locality'] . $address['administrative_area'] . $address['postal_code'] . '<br />' . $country);
    }
    return $customer_information;
  }

  /**
   *
   */
  public function isRealDate($date = NULL) {
    if (FALSE === strtotime($date)) {
      return FALSE;
    }
    [$month, $day, $year] = explode('/', $date);
    return checkdate($month, $day, $year);
  }

  /**
   *
   */
  public function checkFilters($array) {
    $counter = 0;
    $key_exclude = ['submit', 'reset', 'form_build_id', 'form_id', ''];
    foreach ($array as $key => $value) {
      if (is_array($value)) {
        $counter += $this->checkFilters($value);
      }
      elseif (!in_array($key, $key_exclude) && !empty($value) && $value != 'All') {
        $counter++;
      }
    }
    return $counter;
  }

  /**
   *
   */
  public function getProductVariationTitleSku($vid) {
    $product = ProductVariation::load($vid);
    $variation_title = $product->getTitle();
    $variation_sku = $product->getSku();

    // Return order item label product title + sku.
    return $variation_title . " (" . $variation_sku . ")";
  }

  /**
   *
   */
  public function format_phone_string($phoneNumber) {
    $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

    if (strlen($phoneNumber) > 10) {
      $countryCode = substr($phoneNumber, 0, strlen($phoneNumber) - 10);
      $areaCode = substr($phoneNumber, -10, 3);
      $nextThree = substr($phoneNumber, -7, 3);
      $lastFour = substr($phoneNumber, -4, 4);

      $phoneNumber = '+' . $countryCode . ' (' . $areaCode . ') ' . $nextThree . '-' . $lastFour;
    }
    elseif (strlen($phoneNumber) == 10) {
      $areaCode = substr($phoneNumber, 0, 3);
      $nextThree = substr($phoneNumber, 3, 3);
      $lastFour = substr($phoneNumber, 6, 4);

      $phoneNumber = '(' . $areaCode . ') ' . $nextThree . '-' . $lastFour;
    }
    elseif (strlen($phoneNumber) == 7) {
      $nextThree = substr($phoneNumber, 0, 3);
      $lastFour = substr($phoneNumber, 3, 4);

      $phoneNumber = $nextThree . '-' . $lastFour;
    }

    return $phoneNumber;
  }

  /**
   * {@inheritdoc}
   */
  public function getOrderSource($order = NULL) {
    $order_source = $order->get('field_order_source')->value;
    // Get the allowed values directly from the field_order_source field.
    $allowed_values = $order->get('field_order_source')
      ->getSetting('allowed_values');
    // Return value or other text.
    return array_key_exists($order_source, $allowed_values) ? $allowed_values[$order_source] : $order_source;
  }

  /**
   * {@inheritdoc}
   */
  public function allCharactersSameAsChar($string = NULL, $char = 0) {
    /*$n = strlen($string);
    for ($i = 1; $i < $n; $i++)
    if ($string[$i] != $char)
    return FALSE;

    return TRUE;*/
    if (count(array_count_values(str_split($string))) == 1 && $string[0] == $char) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function hasLeadingChar($string = NULL, $char = 0, $pos = 0) {
    if ($string[$pos] == $char) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function highlightChar($string = NULL, $pos = 0) {
    $n = strlen($string);
    $highlighted_phone = "";
    for ($i = 0; $i < $n; $i++) {
      $highlighted_phone = $highlighted_phone . ($i == $pos ? "[" . $string[$i] . "]" : $string[$i]);
    }

    return $highlighted_phone;
  }

  /**
   * {@inheritdoc}
   */
  public function _get_vocabulary_as_select_options($vid) {
    $terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree($vid);
    $result = ['' => '- Select product location -'];
    foreach ($terms as $term) {
      $result[$term->tid] = $term->name;
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function _get_vocabulary_as_select_options_by_variation_id($variation_id) {
    // Print the targeted entity type field.
    $variation = ProductVariation::load((int) $variation_id);
    $pallets = $variation->get('field_location_pallet')->referencedEntities();
    foreach ($pallets as $term) {
      $tid = $term->get('tid')->value;
      $entity = \Drupal::entityTypeManager()
        ->getStorage("taxonomy_term")
        ->load($tid);
      $name = $entity->getName();
      $result[$tid] = $name;
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function recalculateWeight(Order $order) {
    $totalweight = 0.00;
    if ($order->hasItems()) {

      $items = $order->getItems();
      foreach ($items as $key => $item) {
        $quantity = $item->getQuantity();
        $purchased_entity = $item->getPurchasedEntity();
        if (isset($purchased_entity)) {
          if ($purchased_entity->get('weight')->first() != NULL) {
            $weight = $purchased_entity->get('weight')
              ->first()
              ->toMeasurement();
            $itemweight = ($weight->getNumber() * $quantity);
            $totalweight = $totalweight + $itemweight;
          }
        }
      }
    }
    return $totalweight;
  }

  /**
   * {@inheritdoc}
   */
  public function recalculateShipmentWeight(Shipment $shipment) {
    $totalweight = 0.00;
    if ($shipment->hasItems()) {
      $items = $shipment->getItems();
      foreach ($items as $key => $item) {
        $weight = $item->getWeight();
        $totalweight = $totalweight + $weight->getNumber();
      }
    }

    return $totalweight;
  }

  /**
   * {@inheritdoc}
   */
  public function showRequiredTfaMsg($uid) {
    $msg = '';
    $config = \Drupal::config('tfa.settings');
    $allowed_skips = $config->get('validation_skip');
    $required_roles = array_filter($config->get('required_roles'));
    $user_data = \Drupal::service('user.data')
      ->get('tfa', \Drupal::currentUser()->id(), 'tfa_user_settings');
    // If the user has a role that is required to use TFA, then return TRUE.
    $user_roles = \Drupal::currentUser()->getRoles();
    $arr_r = array_intersect($required_roles, $user_roles);

    if (!empty($user_data)) {
      if (count($arr_r) > 0 && $user_data['validation_skipped'] > 0 && $allowed_skips >= $user_data['validation_skipped']) {
        $enabled = isset($user_data['status']) && $user_data['status'] ? TRUE : FALSE;

        if ($enabled && !empty($user_data['data']['plugins'])) {
          // $msg = t('You have setup two-factor authentication.');
          // TFA is set. Do not show any message
        }
        else {
          $tfa_setup_link = Url::fromRoute('tfa.overview', [
            'user' => $uid,
          ])->toString();
          $msg = t('You are required to setup two-factor authentication <a href="@link">here.</a> You have @remaining attempts left after this you will be unable to login.', [
            '@remaining' => max(0, $allowed_skips - $user_data['validation_skipped']),
            '@link' => $tfa_setup_link,
          ]);
        }
      }
    }
    return $msg;
  }

  /**
   *
   */
  public function getProposedShipment($order, $order_line_items, $title = "") {
    // Loop through order items and add them to shipment.
    $proposed_shipment_items = [];
    foreach ($order_line_items as $order_item) {
      $quantity = $order_item->getQuantity();
      $purchased_entity = $order_item->getPurchasedEntity();

      if ($purchased_entity->get('weight')->isEmpty()) {
        $weight = new Weight(1, WeightUnit::OUNCE);
      }
      else {
        $weight_item = $purchased_entity->get('weight')->first();
        $weight = $weight_item->toMeasurement();
      }

      $shipment_item = new ShipmentItem([
        'order_item_id' => $order_item->id(),
        'title' => $purchased_entity->label(),
        'quantity' => $quantity,
        'weight' => $weight->multiply($quantity),
        'declared_value' => $order_item->getTotalPrice(),
      ]);
      $proposed_shipment_items[] = $shipment_item;
    }

    return new ProposedShipment([
      'type' => 'default',
      'order_id' => $order->id(),
      'items' => $proposed_shipment_items,
      'shipping_profile' => $order->getBillingProfile(),
      'title' => strlen($title) > 0 ? $title . " " . $order->getOrderNumber() : $order->getOrderNumber(),
    ]);

  }

  /*public function isOrderBulk(EntityInterface $order){
  $countBulkItems = 0;
  // Make sure it's a order entity.
  if ($order instanceof OrderInterface) {
  $items = $order->getItems();
  foreach($items as $key => $item){
  $qty = $item->getQuantity();
  $product_id = $item->getPurchasedEntity()->get('product_id')->getValue()[0]['target_id'];
  $product = Product::load($product_id);
  $maxlimit_machine_name_config = \Drupal::config('maxlimit.settings')
  ->get('maxlimit_element','field_qty_max_order');
  if(isset($maxlimit_machine_name_config) && !empty($maxlimit_machine_name_config)){
  $max_limit_field = $maxlimit_machine_name_config;
  } else {
  $max_limit_field = 'field_qty_max_order';
  }
  if (!empty($max_limit_field)) {
  if ($product->get($max_limit_field)) {
  $max = $product->get($max_limit_field)->getValue()[0]['value'];
  if ($qty > $max) {
  $countBulkItems++;
  }
  }
  }
  }
  }

  return $countBulkItems > 0 ? TRUE : FALSE;
  }*/
}
