<?php

namespace Drupal\samhsa_pep_migrate_custom;

use Drupal\taxonomy\Entity\Term;
use Drupal\migrate\Row;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate_files_and_images\Entity\FilesCrossReference;
use Drupal\samhsa_pep_migrate_custom\Entity\UnconvertedLinks;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SetMigratingValues.
 *
 * @package Drupal\samhsa_pep_migrate_custom
 */
class SetMigratingValues implements SetMigratingValuesInterface {

  private $folderPattern = '/\/sites\/default\/files\//';
  private $protocolPattern = ['/http:\/\//', '/https:\/\//'];
  /**
   * Private $publicFolder = '/sites/default/files/d7/';.
   */
  private $publicFolder = '/sites/default/files/d7/public/';
  private $publicUrl = NULL;
  /**
   * @todo Set $baseUrl and $basePath accordingly before definitive migration.
   */
  private $baseUrl = '';
  private $basePath = '';
  private $excludedEnxtensions = [
    'com',
    'edu',
    'org',
    'net',
    'gov',
    'html',
    'shtml',
    'htm',
    'asp',
    'aspx',
    'php,',
  ];
  private $imagesExtension = [
    'jpg',
    'jpeg',
    'png',
    'svg',
    'gif',
    'bmp',
    'tif',
    'tiff',
    'ico',
    'xbm',
  ];
  private $filesExtension = [
    'txt',
    'pdf',
    'doc',
    'docx',
    'xls',
    'xlsx',
    'ppt',
    'pptx',
    'zip',
    'wmv',
    'mov',
  ];

  /**
   * Constructor.
   */
  public function __construct() {
    $this->basePath = getcwd();
    $request = Request::createFromGlobals();
    $this->baseUrl = $request->getBaseUrl();
    $this->publicUrl = $this->baseUrl . $this->publicFolder;
    $a = 1;
  }

  /**
   * {@inheritdoc}
   */
  public function simpleField(SqlBase $sql_base, Row &$row = NULL, $components = [], $manipulation_method = NULL) {
    $query = 'SELECT ' .
      $components['field'] .
      ' FROM ' .
      $components['table'] .
      ' WHERE entity_id = ' .
      $row->getSourceProperty($components['idfield']);
    $result = $sql_base->getDatabase()->query($query);
    $values = [];
    foreach ($result as $record) {
      if ($manipulation_method && method_exists($this, $manipulation_method)) {
        $values[] = $this->$manipulation_method($record->$components['field']);
      }
      else {
        $values[] = $record->$components['field'];
      }
    }
    $row->setSourceProperty($components['property'], $values);
  }

  /**
   * {@inheritdoc}
   */
  public function textAreaAndSummary(SqlBase $sql_base, Row &$row = NULL, $components = []) {
    $name_value = $components['field_prefix'] . '_value';
    $name_summary = $components['field_prefix'] . '_summary';
    $name_format = $components['field_prefix'] . '_format';
    $query = "SELECT $name_value, $name_summary, $name_format" .
      ' FROM ' .
      $components['table'] .
      ' WHERE entity_id = ' .
      $row->getSourceProperty('nid');
    $result = $sql_base->getDatabase()->query($query);
    $values = [];
    foreach ($result as $record) {
      $values[] = [
        'value' => property_exists($record, $name_value) ? $this->convertReferencesAndSources($record->$name_value, $row->getSourceProperty('nid'), $this->contentType) : '',
        'summary' => property_exists($record, $name_summary) ? $this->convertReferencesAndSources($record->$name_summary, $row->getSourceProperty('nid'), $this->contentType) : '',
        'format' => property_exists($record, $name_format) ? $this->getTextFormat($record->$name_format) : 0,
      ];
    }
    $row->setSourceProperty($components['property'], $values);
  }

  /**
   * {@inheritdoc}
   */
  public function termReference(SqlBase $sql_base, Row &$row = NULL, $components = []) {
    $query = 'SELECT GROUP_CONCAT( ' .
      $components['field'] .
      ') AS tids FROM ' .
      $components['table'] .
      ' WHERE entity_id = ' .
      $row->getSourceProperty('nid');
    $result = $sql_base->getDatabase()->query($query);
    foreach ($result as $record) {
      if (!is_null($record->tids)) {
        $row->setSourceProperty($components['property'], explode(',', $record->tids));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function entityReference(SqlBase $sql_base, Row &$row = NULL, $components = []) {
    $query = 'SELECT GROUP_CONCAT( ' .
            $components['field'] .
            ') AS tids FROM ' .
            $components['table'] .
            ' WHERE entity_id = ' .
            $row->getSourceProperty('nid');
    $result = $sql_base->getDatabase()->query($query);

    foreach ($result as $record) {
      \Drupal::logger('samhsa_pep_migrate_custom')->notice($row->getSourceProperty('nid') . "/" . $record->tids);

      if (!is_null($record->tids)) {
        $row->setSourceProperty($components['property'], explode(',', $record->tids));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function formattedText(SqlBase $sql_base, Row &$row = NULL, $components = []) {
    $body_files_and_images = [];
    $name_value = $components['fields_prefix'] . '_value';
    $name_format = $components['fields_prefix'] . '_format';
    $query = 'SELECT ' .
      $name_value . ',' .
      $name_format . ' FROM ' .
      $components['table'] .
      ' WHERE entity_id = ' .
      $row->getSourceProperty('nid');
    $result = $sql_base->getDatabase()->query($query);
    $values = [];
    foreach ($result as $record) {
      $body_files_and_images += $this->extractImagesAndFiles($record->$name_value);
      $values[] = [
        'value' => $this->convertReferencesAndSources($record->$name_value, $row->getSourceProperty('nid'), $this->contentType),
        'format' => $this->getTextFormat($record->$name_format),
      ];
    }
    $row->setSourceProperty($components['property'], $values);
  }

  /**
   * {@inheritdoc}
   */
  public function images(SqlBase $sql_base, Row &$row = NULL, $components = []) {
    $name_fid = $components['fields_prefix'] . '_fid';
    $name_alt = $components['fields_prefix'] . '_alt';
    $name_title = $components['fields_prefix'] . '_title';
    $name_width = $components['fields_prefix'] . '_width';
    $name_height = $components['fields_prefix'] . '_height';
    $query = 'SELECT ' .
      $name_fid . ',' .
      $name_alt . ',' .
      $name_title . ',' .
      $name_width . ',' .
      $name_height . ' FROM ' .
      $components['table'] .
      ' WHERE entity_id = ' .
    $row->getSourceProperty('nid');
    $result = $sql_base->getDatabase()->query($query);
    $images = [];
    foreach ($result as $record) {
      $id = $this->getNewFileId($record->$name_fid);
      $images[$id] = [
        'target_id' => $id,
        'alt' => $record->$name_alt,
        'title' => $record->$name_title,
        'width' => $record->$name_width,
        'height' => $record->$name_height,
      ];
    }
    if (isset($components['body_images'])) {
      foreach ($components['body_images'] as $body_image) {
        $p = strrpos($body_image['file_name'], '.');
        if ($p !== FALSE) {
          $extension = strtolower(substr($body_image['file_name'], $p + 1));
          if (in_array($extension, $this->imagesExtension)) {
            $id = $this->getNewFileIdByName($body_image['file_name']);
            $file_path = $file = $this->basePath . $this->publicFolder . $body_image['file_name'];
            if (file_exists($file_path) && !isset($images[$id])) {
              $dimensions = $this->getImageDimensions($file_path);
              $images[$id] = [
                'target_id' => $id,
                'alt' => $body_image['alt_text'],
                'title' => NULL,
                'width' => $dimensions['width'],
                'height' => $dimensions['height'],
              ];
            }
          }
        }
      }
    }
    $row->setSourceProperty($components['property'], $images);
  }

  /**
   * Get width and height of a image.
   *
   * @param string $file_path
   *   The name of image file.
   *
   * @return array
   *   Associative array with width and height.
   */
  private function getImageDimensions($file_path) {
    [$width, $height] = getimagesize($file_path);
    return ['width' => $width, 'height' => $height];
  }

  /**
   * {@inheritdoc}
   */
  public function files(SqlBase $sql_base, Row &$row = NULL, $components = []) {
    $name_fid = $components['fields_prefix'] . '_fid';
    $name_display = $components['fields_prefix'] . '_display';
    $name_description = $components['fields_prefix'] . '_description';
    $query = 'SELECT ' .
      $name_fid . ',' .
      $name_display . ',' .
      $name_description . ' FROM ' .
      $components['table'] .
      ' WHERE entity_id = ' .
      $row->getSourceProperty($components['idfield']);
    $result = $sql_base->getDatabase()->query($query);
    $files = [];
    foreach ($result as $record) {
      $id = $this->getNewFileId($record->$name_fid);
      $files[$id] = [
        'target_id' => $id,
        'display' => 1,
        'description' => $record->$name_description,
      ];
    }

    if (isset($components['body_files'])) {
      foreach ($components['body_files'] as $body_file) {
        $p = strrpos($body_file['file_name'], '.');
        if ($p !== FALSE) {
          $extension = strtolower(substr($body_file['file_name'], $p + 1));
          if (in_array($extension, $this->filesExtension)) {
            $id = $this->getNewFileIdByName($body_file['file_name']);
            if (!isset($files[$id])) {
              $files[$id] = [
                'target_id' => $id,
                'display' => 1,
                'description' => $body_file['inner_html'],
              ];
            }
          }
        }
      }
    }
    $row->setSourceProperty($components['property'], $files);
  }

  /**
   * {@inheritdoc}
   */
  public function files_with_display_name(SqlBase $sql_base, Row &$row = NULL, $components = []) {
    $name_fid = $components['fields_prefix'] . '_fid';
    $name_display = $components['fields_prefix'] . '_display';
    $name_description = $components['fields_prefix'] . '_description';
    $query = 'SELECT ' .
            $name_fid . ',' .
            $name_display . ',' .
            $name_description . ' FROM ' .
            $components['table'] .
            ' WHERE entity_id = ' .
            $row->getSourceProperty($components['idfield']);
    $result = $sql_base->getDatabase()->query($query);
    $files = [];
    foreach ($result as $record) {
      $id = $this->getNewFileId($record->$name_fid);
      $display_name = $this->getDisplayName($sql_base, $record->$name_fid, $components);
      $files[$id] = [
        'target_id' => $id,
        'display' => 1,
        'description' => $display_name,
      ];
    }
    $row->setSourceProperty($components['property'], $files);
  }

  /**
   * {@inheritdoc}
   */
  public function link(SqlBase $sql_base, Row &$row = NULL, $components = []) {
    $name_url = $components['fields_prefix'] . '_url';
    $name_title = $components['fields_prefix'] . '_title';
    $name_attributes = $components['fields_prefix'] . '_attributes';
    $query = 'SELECT ' .
      $name_url . ',' .
      $name_title . ',' .
      $name_attributes . ' FROM ' .
      $components['table'] .
      ' WHERE entity_id = ' .
      $row->getSourceProperty('nid');
    $result = $sql_base->getDatabase()->query($query);
    $values = [];
    foreach ($result as $record) {
      $values[] = [
        'uri' => $record->$name_url,
        'title' => $record->$name_title,
        'options' => $record->$name_attributes,
      ];
    }
    $row->setSourceProperty($components['property'], $values);
  }

  /**
   * {@inheritdoc}
   */
  public function dateIso(SqlBase $sql_base, Row &$row = NULL, $components = []) {
    $this->simpleField($sql_base, $row, $components);
  }

  /**
   * {@inheritdoc}
   */
  public function dateIsoEnd(SqlBase $sql_base, Row &$row = NULL, $components = []) {
    $name_start = $components['field'];
    $name_end = $components['field'] . '2';
    $query = 'SELECT ' .
      $name_start . ',' .
      $name_end . ' FROM ' .
      $components['table'] .
      ' WHERE entity_id = ' .
      $row->getSourceProperty('nid');
    $result = $sql_base->getDatabase()->query($query);
    $values = [];
    foreach ($result as $record) {
      $start_date = explode('T', $record->$name_start);
      $end_date = explode('T', $record->$name_end);
      $values[] = [
        'value' => $start_date[0],
        'end_value' => $end_date[0],
      ];
    }
    $row->setSourceProperty($components['property'], $values);
  }

  /**
   * {@inheritdoc}
   */
  public function dateIsoToYear(SqlBase $sql_base, Row &$row = NULL, $components = []) {
    $query = 'SELECT ' .
      $components['field'] .
      ' FROM ' .
      $components['table'] .
      ' WHERE entity_id = ' .
      $row->getSourceProperty('nid');
    $result = $sql_base->getDatabase()->query($query);
    $values = [];
    foreach ($result as $record) {
      $values[] = date('Y', strtotime($record->$components['field']));
    }
    $row->setSourceProperty($components['property'], $values);
  }

  /**
   * {@inheritdoc}
   */
  public function yesNoToBool(SqlBase $sql_base, Row &$row = NULL, $components = []) {
    $query = 'SELECT ' .
      $components['field'] .
      ' FROM ' .
      $components['table'] .
      ' WHERE entity_id = ' .
      $row->getSourceProperty('nid');
    $result = $sql_base->getDatabase()->query($query);
    $values = [];
    foreach ($result as $record) {
      $yes_no = strtolower($record->$components['field']);
      switch ($yes_no) {
        case 'yes':
          $values[] = 1;
          break;

        case 'no':
          $values[] = 0;
          break;

        default:
          $values[] = NULL;

      }
      $values[] = $record->$components['field'];
    }
    $row->setSourceProperty($components['property'], $values);
  }

  /**
   *
   */
  public function dateOnly(SqlBase $sql_base, Row &$row = NULL, $components = [], $manipulation_method = NULL) {
    $query = 'SELECT ' .
      $components['field'] .
      ' FROM ' .
      $components['table'] .
      ' WHERE entity_id = ' .
      $row->getSourceProperty('nid');
    $result = $sql_base->getDatabase()->query($query);
    $values = [];
    foreach ($result as $record) {
      $pieces = explode('T', $record->$components['field']);
      $values[] = $pieces[0];
    }
    $row->setSourceProperty($components['property'], $values);
  }

  /**
   * {@inheritdoc}
   */
  public function multiValueToTextArea(SqlBase $sql_base, Row &$row = NULL, $components = []) {
    $query = 'SELECT ' .
      $components['field'] .
      ' FROM ' .
      $components['table'] .
      ' WHERE entity_id = ' .
      $row->getSourceProperty('nid');
    $result = $sql_base->getDatabase()->query($query);
    $values = [];
    foreach ($result as $record) {
      $values[] = $record->$components['field'];
    }
    $glue = $components['delimiter'] ?? "\r\n";
    $values = implode($glue, $values);
    $row->setSourceProperty($components['property'], $values);
  }

  /**
   * {@inheritdoc}
   */
  public function getNewFileId($fid = NULL) {
    if ($entity = FilesCrossReference::load($fid)) {
      return @$entity->d8fid->getString();
    }
    else {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDisplayName(SqlBase $sql_base, $fid = NULL, $components = []) {
    $display_name = "";
    $tbl_name = $components['display_name_tbl'];
    $field_name = $components['display_name_field'];

    if (isset($components['display_name_tbl'])) {
      $query = 'SELECT ' .
                $components['display_name_field'] .
                ' FROM ' .
                $components['display_name_tbl'] .
                ' WHERE entity_id = ' .
                $fid;
      $result = $sql_base->getDatabase()->query($query);
      $values = [];
      foreach ($result as $record) {
        $values[] = $record->$field_name;
      }
      if (count($values) > 0) {
        $display_name = trim($values[0]);
      }
      return $display_name;
    }
    else {
      return "";
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getNewFileIdByName($name = NULL) {
    if ($obj = FilesCrossReference::loadByName($name)) {
      return $obj->d8fid;
    }
    else {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function extractImagesAndFiles($text = NULL) {
    if ($text) {
      $images = $this->getImagesReferences($text);
      $files = $this->getFilesReferences($text);
      return array_merge($images, $files);
    }
    else {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getImagesReferences($text = NULL) {
    $result = [];
    if ($text) {
      $doc = new \DOMDocument();
      @$doc->loadHTML($text, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
      $images_tags = $doc->getElementsByTagName('img');
      foreach ($images_tags as $images_tag) {
        $src = $images_tag->getAttribute('src');
        $file_name = preg_replace($this->folderPattern, '', $src, 1);
        $file_name_items = explode('/', $file_name);
        $file_name = array_pop($file_name_items);
        $file_path = $file = $this->basePath . $this->publicFolder . $file_name;
        $alt = $images_tag->getAttribute('alt');
        $result[$file_name] = [
          'file_name' => $file_name,
          'alt_text' => substr($alt, 0, 512),
          'inner_html' => NULL,
        ];
      }
    }
    return array_filter($result);
  }

  /**
   * {@inheritdoc}
   */
  public function getFilesReferences($text = NULL) {
    $result = [];
    if ($text) {
      $doc = new \DOMDocument();
      @$doc->loadHTML($text, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
      $a_tags = $doc->getElementsByTagName('a');
      foreach ($a_tags as $a_tag) {
        $href = preg_replace($this->folderPattern, '', $a_tag->getAttribute('href'), 1);
        $href = preg_replace($this->protocolPattern, '', $href);
        $href_items = explode('#', $href);
        $href_items = explode('/', $href_items[0]);
        if (!$file_name = array_pop($href_items)) {
          $file_name = array_pop($href_items);
        }
        if (preg_match('/\./', $file_name)) {
          $name_items = explode('.', $file_name);
          if ($name_items[0] != 'www' && !in_array(array_pop($name_items), $this->excludedEnxtensions)) {
            $result[$file_name] = [
              'file_name' => $file_name,
              'alt_text' => NULL,
              'inner_html' => $a_tag->nodeValue,
            ];
          }
        }
      }
    }
    return array_filter($result);
  }

  /**
   * {@inheritdoc}
   */
  public function convertReferencesAndSources($text, $nid = 0, $bundle = NULL) {
    if ($text) {
      $doc = new \DOMDocument();
      @$doc->loadHTML('<?xml encoding="UTF-8">' . $text, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
      $images_tags = $doc->getElementsByTagName('img');
      foreach ($images_tags as $images_tag) {
        $src = explode('/', $images_tag->getAttribute('src'));
        $file_name = $this->publicUrl . array_pop($src);
        $images_tag->setAttribute('src', $file_name);
      }
      $anchor_tags = $doc->getElementsByTagName('a');
      foreach ($anchor_tags as $anchor_tag) {
        $href = $anchor_tag->getAttribute('href');
        $href_pieces = explode('/', $href);
        $file_name = trim(array_pop($href_pieces));
        $file_url = $this->publicUrl . $file_name;
        $file_path = $this->basePath . $this->publicFolder . $file_name;
        if ($file_name && file_exists($file_path)) {
          $anchor_tag->setAttribute('href', $file_url);
          $this->deleteUnconvertedLink($href, $nid);
        }
        else {
          $this->storeUnconvertedLink($href, $nid, $bundle);
        }
      }
      $result = $doc->saveHTML();
      $result = str_replace('<?xml encoding="UTF-8">', '', $result);
    }
    else {
      $result = NULL;
    }
    return $result;
  }

  /**
   *
   */
  public function convertTextListToVocabularyEntry(SqlBase $sql_base, Row &$row = NULL, $components = []) {
    $vocabulary = $components['vocabulary'];
    $query = 'SELECT ' .
      $components['field'] .
      ' FROM ' .
      $components['table'] .
      ' WHERE entity_id = ' .
      $row->getSourceProperty('nid');
    $result = $sql_base->getDatabase()->query($query);
    foreach ($result as $record) {
      $results = taxonomy_term_load_multiple_by_name($record->$components['field'], $vocabulary);
      if (sizeof($results) > 0) {
        $term = array_pop($results);
        $tid = $term->id();
      }
      // Creation needed.
      else {
        $term = Term::create([
          'name' => $record->$components['field'],
          'vid' => $vocabulary,
        ]);
        $term->save();
        $tid = $term->id();
      }
      $row->setSourceProperty($components['property'], $tid);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function storeUnconvertedLink($url = NULL, $nid = 0, $bundle = NULL) {
    if ($url) {
      $id = $this->hashOfTheUrl($url) . '-' . $nid;
      if (!$entity = UnconvertedLinks::load($id)) {
        $values = [
          'id' => $id,
          'url' => substr($url, 0, 510),
          'bundle' => $bundle,
          'nid' => $nid,
        ];
        $entity = UnconvertedLinks::create($values);
        $entity->save();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function deleteUnconvertedLink($url = NULL, $nid = 0) {
    if ($url) {
      $id = $this->hashOfTheUrl($url) . '-' . $nid;
      if ($entity = UnconvertedLinks::load($id)) {
        $entity->delete();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function hashOfTheUrl($url = NULL) {
    return hash_hmac('ripemd160', $url, 'd8');
  }

  /**
   * {@inheritdoc}
   */
  public function getTextFormat($format) {
    return 'full_html';
  }

  /**
   *
   */
  public function convertDateStrToYear($value) {
    return $value ? substr($value, 0, 4) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function userReference(SqlBase $sql_base, Row &$row = NULL, $components = [], $manipulation_method = NULL) {
    $query = 'SELECT ' .
      $components['field'] .
      ' FROM ' .
      $components['table'] .
      ' WHERE entity_id = ' .
      $row->getSourceProperty('uid');
    $result = $sql_base->getDatabase()->query($query);
    foreach ($result as $record) {
      $values[] = $record->$components['field'];
    }
    $row->setSourceProperty($components['property'], $values);
  }

  /**
   * {@inheritdoc}
   */
  public function nodeReference(SqlBase $sql_base, Row &$row = NULL, $components = [], $manipulation_method = NULL) {
    $query = 'SELECT ' .
      $components['field'] .
      ' FROM ' .
      $components['table'] .
      ' WHERE entity_id = ' .
      $row->getSourceProperty('nid');
    $result = $sql_base->getDatabase()->query($query);
    foreach ($result as $record) {
      $values[] = $record->$components['field'];
    }
    $row->setSourceProperty($components['property'], $values);
  }

  /**
   * {@inheritdoc}
   */
  public function timestampToDateString($value) {
    return date('Y-m-d', strtotime($value));
  }

}
