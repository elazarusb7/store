<?php

namespace Drupal\samhsa_index_field_switch;

use Drupal\Component\Datetime\DateTimePlus;

/**
 * Class FieldSwitchTypecasting.
 *
 * @package Drupal\samhsa_index_field_switch
 */
class FieldSwitchTypecasting {

  /**
   * Value of the From field.
   *
   * @var string
   */
  private static $fromValue = NULL;

  /**
   * Format of the From field.
   *
   * @var string
   */
  private static $fromFormat = NULL;

  /**
   * Format of the To field.
   *
   * @var string
   */
  private static $toFormat = NULL;

  /**
   * FieldSwitchTypecasting constructor.
   *
   * @param mixed $value
   *   Value to be converted.
   * @param string $from_format
   *   Format of the input value.
   * @param string $to_format
   *   Format of the output value.
   */
  public function __construct($value = NULL, $from_format = '', $to_format = '') {
    self::$fromValue = $value;
    self::$fromFormat = $from_format;
    self::$toFormat = $to_format;
  }

  /**
   * Converts date string to timestamp.
   *
   * @return int
   *   Timestamp.
   */
  public static function stringToTimestamp() {
    $from_format = self::$fromFormat ? self::$fromFormat : 'Y-m-d';
    try {
      $date = DateTimePlus::createFromFormat($from_format, (string) self::$fromValue);
      $result = $date->getTimestamp();
    }
    catch (\Exception $e) {
      $result = NULL;
    }
    return $result;
  }

  /**
   * Converts date string to timestamp.
   *
   * @return int
   *   Timestamp.
   */
  public static function dateToTimestamp() {
    return self::stringToTimestamp();
  }

  /**
   * Converts date string to date.
   *
   * @return string
   *   String.
   */
  public static function stringToDate() {
    $from_format = self::$fromFormat ? self::$fromFormat : 'Y-m-d';
    try {
      $date = DateTimePlus::createFromFormat($from_format, (string) self::$fromValue);
      $result = $date->format(self::$toFormat);
    }
    catch (\Exception $e) {
      $result = NULL;
    }
    return $result;
  }

  /**
   * Converts date string to date.
   *
   * @return string
   *   String.
   */
  public static function dateToDate() {
    return self::stringToDate();
  }

  /**
   * Converts timestamp to date.
   *
   * @return string
   *   String.
   */
  public static function timestampToDate() {
    try {
      $date = DateTimePlus::createFromTimestamp((integer) self::$fromValue);
      $result = $date->format(self::$toFormat);
    }
    catch (\Exception $e) {
      $result = NULL;
    }
    return $result;
  }

}
