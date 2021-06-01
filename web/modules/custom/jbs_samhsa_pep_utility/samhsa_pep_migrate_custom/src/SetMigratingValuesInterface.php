<?php

/**
 * @file
 * Contains \Drupal\samhsa_pep_migrate_custom\SetMigratingValuesInterface.
 */

namespace Drupal\samhsa_pep_migrate_custom;

use Drupal\migrate\Row;
use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Interface SetMigratingValuesInterface.
 *
 * @package Drupal\samhsa_pep_migrate_custom
 */
interface SetMigratingValuesInterface {

  /**
   * Populates regular fields of the migrating row.
   *
   * @param SqlBase $sql_base
   *   The migrating object.
   * @param Row $row
   *   The row extracted from the node query results.
   * @param array $components
   *   The components of the query.
   *     field: field name
   *     table: table name
   *     property: alias for the field, as defined in the configuration ymls.
   *     idfield: name of the identity field: nid, uid, etc./
   */
  public function simpleField(SqlBase $sql_base, Row &$row = NULL, $components = array());

  /**
   * Populates taxonomy fields of the migrating row.
   *
   * @param SqlBase $sql_base
   *   The migrating object.
   * @param Row $row
   *   The row extracted from the node query results.
   * @param array $components
   *   The components of the query.
   *     field: field name
   *     table: table name
   *     property: alias for the field, as defined in the configuration ymls.
   */
  public function entityReference(SqlBase $sql_base, Row &$row = NULL, $components = array());

    /**
     * Populates taxonomy fields of the migrating row.
     *
     * @param SqlBase $sql_base
     *   The migrating object.
     * @param Row $row
     *   The row extracted from the node query results.
     * @param array $components
     *   The components of the query.
     *     field: field name
     *     table: table name
     *     property: alias for the field, as defined in the configuration ymls.
     */
    public function termReference(SqlBase $sql_base, Row &$row = NULL, $components = array());

  /**
   * Populates formatted texts fields of the migrating row.
   *
   * @param SqlBase $sql_base
   *   The migrating object.
   * @param Row $row
   *   The row extracted from the node query results.
   * @param array $components
   *   The components of the query.
   *     field: field name
   *     table: table name
   *     property: alias for the field, as defined in the configuration ymls.
   */
  public function formattedText(SqlBase $sql_base, Row &$row = NULL, $components = array());

  /**
   * Populates image fields of the migrating row.
   *
   * @param SqlBase $sql_base
   *   The migrating object.
   * @param Row $row
   *   The row extracted from the node query results.
   * @param array $components
   *   The components of the query.
   *     field: field name
   *     table: table name
   *     property: alias for the field, as defined in the configuration ymls.
   */
  public function images(SqlBase $sql_base, Row &$row = NULL, $components = array());

  /**
   * Populates file fields of the migrating row.
   *
   * @param SqlBase $sql_base
   *   The migrating object.
   * @param Row $row
   *   The row extracted from the node query results.
   * @param array $components
   *   The components of the query.
   *     field: field name
   *     table: table name
   *     property: alias for the field, as defined in the configuration ymls..
   */
  public function files_with_display_name(SqlBase $sql_base, Row &$row = NULL, $components = array());

    /**
     * Populates file fields of the migrating row.
     *
     * @param SqlBase $sql_base
     *   The migrating object.
     * @param Row $row
     *   The row extracted from the node query results.
     * @param array $components
     *   The components of the query.
     *     field: field name
     *     table: table name
     *     property: alias for the field, as defined in the configuration ymls..
     */
    public function files(SqlBase $sql_base, Row &$row = NULL, $components = array());
  /**
   * Populates link fields of the migrating row.
   *
   * @param SqlBase $sql_base
   *   The migrating object.
   * @param Row $row
   *   The row extracted from the node query results.
   * @param array $components
   *   The components of the query.
   *     field: field name
   *     table: table name
   *     property: alias for the field, as defined in the configuration ymls.
   */
  public function link(SqlBase $sql_base, Row &$row = NULL, $components = array());

  /**
   * Populates date fields of the migrating row.
   *
   * @param SqlBase $sql_base
   *   The migrating object.
   * @param Row $row
   *   The row extracted from the node query results.
   * @param array $components
   *   The components of the query.
   *     field: field name
   *     table: table name
   *     property: alias for the field, as defined in the configuration ymls.
   */
  public function dateIso(SqlBase $sql_base, Row &$row = NULL, $components = array());

  /**
   * Populates integer "field_year" of the migrating row.
   *
   * @param SqlBase $sql_base
   *   The migrating object.
   * @param Row $row
   *   The row extracted from the node query results.
   * @param array $components
   *   The components of the query.
   *     field: field name
   *     table: table name
   *     property: alias for the field, as defined in the configuration ymls.
   */
  public function dateIsoToYear(SqlBase $sql_base, Row &$row = NULL, $components = array());

  /**
   * Populates Archive Content field of the migrating row.
   *
   * @param SqlBase $sql_base
   *   The migrating object.
   * @param Row $row
   *   The row extracted from the node query results.
   * @param array $components
   *   The components of the query.
   *     field: field name
   *     table: table name
   *     property: alias for the field, as defined in the configuration ymls.
   */
  public function yesNoToBool(SqlBase $sql_base, Row &$row = NULL, $components = array());

  /**
   * Populates Text area (multiple rows) fields with multi values.
   *
   * @param SqlBase $sql_base
   *   The migrating object.
   * @param Row $row
   *   The row extracted from the node query results.
   * @param array $components
   *   The components of the query.
   *     field: field name
   *     table: table name
   *     property: alias for the field, as defined in the configuration ymls.
   */
  public function multiValueToTextArea(SqlBase $sql_base, Row &$row = NULL, $components = array());

  /**
   * Consults file_cross_reference table in order to get the new fid.
   *
   * @param string $fid
   *   The D7 fid.
   *
   * @return int
   *   The new D8 fid.
   */
  public function getNewFileId($fid = NULL);

    /**
     * Get Display name from the field_data_field_samhsa_display_name table.
     *
     * @param string $fid
     *   The D7 fid.
     *
     * @return string
     *   The display_name.
     */
    public function getDisplayName(SqlBase $sql_base, $fid = NULL, $components = array());

  /**
   * Consults file_cross_reference table in order to get the new fid.
   *
   * @param string $name
   *   The file name.
   *
   * @return int
   *   The new D8 fid.
   */
  public function getNewFileIdByName($name = NULL);

  /**
   * Extract all references to images and files from a given text.
   *
   * @param string $text
   *   The text string.
   *
   * @return array
   *   An array with all images file names.
   */
  public function extractImagesAndFiles($text = NULL);

  /**
   * Extract all references to images from a given text.
   *
   * @param string $text
   *   The text string.
   *
   * @return array
   *   An array with all images file names.
   */
  public function getImagesReferences($text = NULL);

  /**
   * Extract all references to files from a given text.
   *
   * @param string $text
   *   The text string.
   *
   * @return array
   *   An array with all images file names.
   */
  public function getFilesReferences($text = NULL);

  /**
   * Changes the values of all images src attribute.
   *
   * @param string $text
   *   The html text string.
   * @param int $nid
   *   The node ID.
   * @param string $bundle
   *   The content type.
   *
   * @return string
   *   The changes html text string.
   */
  public function convertReferencesAndSources($text, $nid = 0, $bundle = NULL);

  /**
   * Converts the body format flag, if necessary.
   *
   * @param string $format
   *   The text format.
   *
   * @return string
   *   The text format.
   */
  public function getTextFormat($format);

  /**
   * Saves the unconverted links.
   *
   * @param string $url
   *   The URL of the unconverted link.
   * @param int $nid
   *   The node ID.
   * @param string $bundle
   *   The content type.
   */
  public function storeUnconvertedLink($url = NULL, $nid = 0, $bundle = NULL);

  /**
   * Deletes the unconverted links.
   *
   * @param string $url
   *   The URL of the unconverted link.
   * @param int $nid
   *   The node ID.
   */
  public function deleteUnconvertedLink($url = NULL, $nid = 0);

  /**
   * Hashes th URL.
   *
   * @param string $url
   *   The URL.
   *
   * @return string
   *   The hashed value of the URL.
   */
  public function hashOfTheUrl($url = NULL);

}
