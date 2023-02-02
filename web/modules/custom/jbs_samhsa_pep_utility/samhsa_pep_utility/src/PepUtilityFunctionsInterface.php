<?php

namespace Drupal\samhsa_pep_utility;

use Drupal\commerce_order\Entity\Order;

/**
 * Interface PepUtilityFunctionsInterface.
 *
 * @package Drupal\samhsa_pep_utility
 */
interface PepUtilityFunctionsInterface {

  /**
   *
   * @param $order
   *   The Commerce Order.
   *
   * @return address string
   *   The order user default customer profile address.
   */
  public function getCustomerDefaultProfileAddress($order = NULL);

  /**
   *
   * @param $date
   *   The Date String.
   *
   * @return TrueFalse
   *   If Date is valid.
   */
  public function IsRealDate($date = NULL);

  /**
   *
   * @param $array
   *   The Array of Exposed Filter Values.
   *
   * @return Count of filters that have data
   */
  public function checkFilters($array);

  /**
   *
   * @param $vid
   *   Product Variation ID.
   *
   * @return Product Variation title + sku
   */
  public function getProductVariationTitleSku($vid);

  /**
   *
   * @param $order
   *   Commerce Order.
   *
   * @return boolean true/false
   * check if order is bulk. At least one item in the order should be over the max limit
   */
  // Public function isOrderBulk(EntityInterface $order);.

  /**
   *
   * @param $raw_number
   *   Phone Number not formatted.
   *
   * @return Formatted phoen number (123) 456-7890
   */
  public function format_phone_string($raw_number);

  /**
   *
   * @param $order
   *   The Commerce Order.
   *
   * @return order source string
   *   The order source string.
   */
  public function getOrderSource($order = NULL);

  /**
   *
   * @param $string
   *   String to check.
   * @param %char
   *   Character whole string contains of
   * @return bool
   *   The TRUE/FALSE.
   */
  public function allCharactersSameAsChar(string $string = NULL, $char);

  /**
   *
   * @param $string
   *   String to check.
   * @param %char
   *   Character which string startes with
   * @param %pos
   *   Character position to be checked
   * @return bool
   *   The TRUE/FALSE.
   */
  public function hasLeadingChar(string $string = NULL, $char, $pos);

  /**
   *
   * @param $string
   *   String to check.
   * @param %pos
   *   Character position to be checked
   * @return string with highlighted char
   *   The String
   */
  public function highlightChar(string $string = NULL, $pos);

  /**
   * Gets all terms for a vocabulary.
   *
   * @param string $vid
   *   Vocabulaty ID.
   *
   * @return array
   *   Associative array with the vocabulary terms.
   */
  public function _get_vocabulary_as_select_options($vid);

  /**
   * Gets all terms for a vocabulary.
   *
   * @param int $variation_id
   *   Variation ID.
   *
   * @return array
   *   Associative array with the vocabulary terms.
   */
  public function _get_vocabulary_as_select_options_by_variation_id($variation_id);

  /**
   * Gets order weight.
   *
   * @param entity $order
   *   Order ID.
   *
   * @return float
   *   order weight.
   */
  public function recalculateWeight(Order $order);

  /**
   * Gets TFA required message.
   *
   * @param int $uid
   *   Order ID.
   *
   * @return z4msg
   */
  public function showRequiredTfaMsg($uid);

  /**
   * Gets proposed shipment.
   *
   * @param entity $order
   *   Order.
   * @param array of order_items entities
   *   Order Items
   * @param string title of the proposed shipment
   *   Order Shipment Title
   *
   * @return proposedShipment
   *   Drupal\commerce_shipping\ProposedShipment
   */
  public function getProposedShipment($order, $order_line_items, $title = "");

}
