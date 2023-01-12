<?php

namespace Drupal\migrate_files_and_images\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\migrate_files_and_images\Entity\FilesCrossReference;
use Masterminds\HTML5\Exception;

/**
 * Class ImportFilesAndImages.
 *
 * @package Drupal\migrate_files_and_images\Controller
 */
class ImportFilesAndImages extends ControllerBase {

  private $folderPattern = '/\/sites\/default\/files\//';

  private $protocolPattern = ['http' => '/http:\/\//', 'https' => '/https:/'];

  // Private $d7PublicFolder = '/sites/default/files/d7/';
  // private $d7PrivateFolder = '/sites/default/files/d7/priv/';
  // private $d7PublicFolder = 'https:/niadev.jbsinternational.com/sites/default/files/';.
  /**
   * Private $d7PublicFolder = 'https://www.nia.nih.gov/sites/default/files/';
   */
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

  public $managedCsvFileName = 'public://managed_files_and_images.csv';

  public $inlineCsvFileName = 'public://inline_files_and_images.csv';

  public $blockCsvFileName = 'public://block_files_and_images.csv';

  /**
   * {@inheritdoc}
   */
  private function getd7PublicFolder() {
    $image_path = $this->config('migrate_files_and_images.settings')
      ->get('images_path');
    return $image_path;
  }

  /**
   * {@inheritdoc}
   */
  private function getd7PrivateFolder() {
    $documents_path = $this->config('migrate_files_and_images.settings')
      ->get('documents_path');
    return $documents_path;
  }

  /**
   * Call the importing process for non CLI triggered execution.
   *
   * @return array
   *   Render array with messages about the execution.
   */
  public function prepareExecuteImport($limit = 0) {
    $output = [];
    if ($this->truncateCrossReference()) {

      $result = $this->executeManagedFilesImport($limit);
      if ($result['managed_error_message']) {
        $output[] = [
          '#type' => 'markup',
          '#markup' => '<p>' . $result['managed_error_message'] . '</p>',
        ];
      }
      else {
        $output[] = [
          '#type' => 'markup',
          '#markup' => '<h3>Migrating Managed Files</h3>',
        ];
        $output[] = [
          '#type' => 'markup',
          '#markup' => '<p>' . $this->t('%counter files imported.', ['%counter' => $result['managed_processed']]) . '</p>',
        ];
        $output[] = [
          '#type' => 'markup',
          '#markup' => '<p>' . $this->t('%counter files not found in public:// directory .', ['%counter' => $result['managed_not_found']]) . '</p>',
        ];
        $output[] = [
          '#type' => 'markup',
          '#markup' => '<a href="' . \Drupal::service('file_url_generator')
            ->generateAbsoluteString($this->managedCsvFileName) . '">CSV inline images</a>',
        ];
      }

      $result = $this->executeInlineImagesImport($limit);
      if ($result['inline_error_message']) {
        $output[] = [
          '#type' => 'markup',
          '#markup' => '<p>' . $result['inline_error_message'] . '</p>',
        ];
      }
      else {
        $output[] = [
          '#type' => 'markup',
          '#markup' => '<br/><br/><br/><h3>Migrating Inline Files in Nodes</h3>',
        ];
        $output[] = [
          '#type' => 'markup',
          '#markup' => '<p>' . $this->t('%counter files imported.', ['%counter' => $result['inline_processed']]) . '</p>',
        ];
        $output[] = [
          '#type' => 'markup',
          '#markup' => '<p>' . $this->t('%counter files not found in public:// directory .', ['%counter' => $result['inline_not_found']]) . '</p>',
        ];
        $output[] = [
          '#type' => 'markup',
          '#markup' => '<a href="' . \Drupal::service('file_url_generator')
            ->generateAbsoluteString($this->inlineCsvFileName) . '">CSV inline images</a>',
        ];
      }

      $result = $this->executeBlockImagesImport($limit);
      if ($result['block_error_message']) {
        $output[] = [
          '#type' => 'markup',
          '#markup' => '<p>' . $result['block_error_message'] . '</p>',
        ];
      }
      else {
        $output[] = [
          '#type' => 'markup',
          '#markup' => '<br/><br/><br/><h3>Migrating Inline Files In Blocks</h3>',
        ];
        $output[] = [
          '#type' => 'markup',
          '#markup' => '<p>' . $this->t('%counter files imported.', ['%counter' => $result['block_processed']]) . '</p>',
        ];
        $output[] = [
          '#type' => 'markup',
          '#markup' => '<p>' . $this->t('%counter files not found in public:// directory .', ['%counter' => $result['block_not_found']]) . '</p>',
        ];
        $output[] = [
          '#type' => 'markup',
          '#markup' => '<a href="' . \Drupal::service('file_url_generator')
            ->generateAbsoluteString($this->blockCsvFileName) . '">CSV block images</a>',
        ];
      }
    }
    else {
      $output[] = [
        '#type' => 'markup',
        '#markup' => '<p>' . $this->t('Could not truncate files_cross_reference table!') . '</p>',
      ];
    }
    return $output;
  }

  /**
   * Delete all records in files_cross_reference table.
   *
   * @return bool
   *   Result of the query.
   */
  public function truncateCrossReference() {
    $db = Database::getConnection();
    $getConnectionOptions = $db->getConnectionOptions();
    $blsad8 = $getConnectionOptions['database'];
    $d8_prefix = $getConnectionOptions['prefix']['default'];

    $result = $db->query("DELETE FROM " . $blsad8 . "." . $d8_prefix . "files_cross_reference;");
    return $result;
  }

  /**
   * Execute Import for managed files.
   *
   * @return array
   *   managed_processed: number of successfully imported images,
   *   managed_not_found: number of images not found in sites/files directory
   */
  public function executeManagedFilesImport($limit = 0) {
    $counter_true = 0;
    $counter_false = 0;
    $error_message = NULL;
    $csv_file = fopen($this->managedCsvFileName, 'w');
    $csv_line = [
      'D7 FID',
      'File name',
      'Status',
      'D8 FID',
    ];
    fputcsv($csv_file, $csv_line);
    try {
      Database::setActiveConnection('migrate');
      $db = Database::getConnection();
      $data = $db->select('file_managed', 'f')->fields('f')->execute();
      foreach ($data as $file_managed) {
        $csv_line = [
          $file_managed->fid,
          $file_managed->filename,
        ];
        if (!strstr($file_managed->uri, 'publication_transactions_rpt') && !strstr($file_managed->uri, 'doc/bulkorders') && !strstr($file_managed->uri, 'doc/otherorders')) {
          if ($fid = $this->copyTheFile($file_managed)) {
            $values = [
              'id' => $file_managed->fid,
              'name' => $file_managed->filename,
              'd8fid' => $fid,
            ];
            $entity = FilesCrossReference::create($values);
            $entity->save();
            $counter_true++;
            $csv_line[] = 'OK';
            $csv_line[] = $fid;
          }
          else {
            $counter_false++;
            $csv_line[] = 'Error';
            $csv_line[] = 0;
          }
        }
        fputcsv($csv_file, $csv_line);
        if ($limit && ($counter_true >= $limit || $counter_false >= $limit)) {
          break;
        }
      }
      Database::setActiveConnection();
    }
    catch (Exception $e) {
      $error_message = $e->getMessage();
    }
    fclose($csv_file);
    return [
      'managed_processed' => $counter_true,
      'managed_not_found' => $counter_false,
      'managed_error_message' => $error_message,
    ];
  }

  /**
   * Execute import for images references in nodes body text.
   *
   * @return array
   *   inline_processed: number of successfully imported images,
   *   inline_not_found: number of images not found in sites/files directory
   *   inline_error_message: exceptions messages
   */
  public function executeInlineImagesImport($limit = 0) {
    $counter_true = 0;
    $counter_false = 0;
    $error_message = NULL;
    $inline_fid = 1000000;
    $csv_file = fopen($this->inlineCsvFileName, 'w');
    $csv_line = [
      'Content type',
      'NID',
      'File name',
      'Status',
      'D8 FID',
    ];
    fputcsv($csv_file, $csv_line);
    try {
      Database::setActiveConnection('migrate');
      $db = Database::getConnection();
      $data = $db->select('field_data_body', 'f')->fields('f')->execute();
      foreach ($data as $body_text) {
        if ($images_references = $this->extractImagesAndFiles($body_text->body_value)) {
          foreach ($images_references as $images_reference) {
            $csv_line = [
              $body_text->bundle,
              $body_text->entity_id,
              $images_reference,
            ];
            if ($fid = $this->copyTheFile($images_reference)) {
              $values = [
                'id' => $inline_fid++,
                'name' => end(explode('/', $images_reference)),
                'd8fid' => $fid,
              ];
              $entity = FilesCrossReference::create($values);
              $entity->save();
              $counter_true++;
              $csv_line[] = 'OK';
              $csv_line[] = $fid;
            }
            else {
              $counter_false++;
              $csv_line[] = 'Error';
              $csv_line[] = 0;
            }
            fputcsv($csv_file, $csv_line);
          }
        }
        if ($limit && ($counter_true >= $limit || $counter_false >= $limit)) {
          break;
        }
      }
      Database::setActiveConnection();
    }
    catch (Exception $e) {
      $error_message = $e->getMessage();
    }
    fclose($csv_file);
    return [
      'inline_processed' => $counter_true,
      'inline_not_found' => $counter_false,
      'inline_error_message' => $error_message,
    ];
  }

  /**
   * Execute import for images references in blocks body text.
   *
   * @return array
   *   block_processed: number of successfully imported images,
   *   block_not_found: number of images not found in sites/files directory
   *   block_error_message: exceptions messages
   */
  public function executeBlockImagesImport($limit = 0) {
    $counter_true = 0;
    $counter_false = 0;
    $error_message = NULL;
    $inline_fid = 2000000;
    $csv_file = fopen($this->blockCsvFileName, 'w');
    $csv_line = [
      'BID',
      'File name',
      'Status',
      'D8 FID',
    ];
    fputcsv($csv_file, $csv_line);
    try {
      Database::setActiveConnection('migrate');
      $db = Database::getConnection();
      $data = $db->select('block_custom', 'f')->fields('f')->execute();
      foreach ($data as $body_text) {
        if ($images_references = $this->extractImagesAndFiles($body_text->body)) {
          foreach ($images_references as $images_reference) {
            $csv_line = [
              $body_text->bid,
              $images_reference,
            ];
            if ($fid = $this->copyTheFile($images_reference)) {
              $values = [
                'id' => $inline_fid++,
                'name' => end(explode('/', $images_reference)),
                'd8fid' => $fid,
              ];
              $entity = FilesCrossReference::create($values);
              $entity->save();
              $counter_true++;
              $csv_line[] = 'OK';
              $csv_line[] = $fid;
            }
            else {
              $counter_false++;
              $csv_line[] = 'Error';
              $csv_line[] = 0;
            }
            fputcsv($csv_file, $csv_line);
          }
        }
        if ($limit && ($counter_true >= $limit || $counter_false >= $limit)) {
          break;
        }
      }
      Database::setActiveConnection();
    }
    catch (Exception $e) {
      $error_message = $e->getMessage();
    }
    fclose($csv_file);
    return [
      'block_processed' => $counter_true,
      'block_not_found' => $counter_false,
      'block_error_message' => $error_message,
    ];
  }

  /**
   * Extract all references to images and files from a given text.
   *
   * @param string $text
   *   The text string.
   *
   * @return array
   *   An array with all images file names.
   */
  private function extractImagesAndFiles($text = NULL) {
    if ($text) {
      $images = $this->getImagesReferences($text);
      $files = $this->getFilesReferences($text);
      return array_unique(array_merge($images, $files));
    }
    else {
      return NULL;
    }
  }

  /**
   * Extract all references to images from a given text.
   *
   * @param string $text
   *   The text string.
   *
   * @return array
   *   An array with all images file names.
   */
  private function getImagesReferences($text = NULL) {
    $result = [];
    if ($text) {
      $doc = new \DOMDocument();
      $doc->loadHTML($text, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
      $images_tags = $doc->getElementsByTagName('img');
      foreach ($images_tags as $images_tag) {
        $src = $images_tag->getAttribute('src');
        $result[] = preg_replace($this->folderPattern, '', $src, 1);
      }
    }
    return array_filter($result);
  }

  /**
   * Extract all references to files from a given text.
   *
   * @param string $text
   *   The text string.
   *
   * @return array
   *   An array with all images file names.
   */
  private function getFilesReferences($text = NULL) {
    $result = [];
    if ($text) {
      $doc = new \DOMDocument();
      $doc->loadHTML($text, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
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
            $result[] = $file_name;
          }
        }
      }
    }
    return array_filter($result);
  }

  /**
   * Copy the file from the D7 files folder to D8.
   *
   * @param object $file_managed
   *   File object.
   *
   * @return bool|int
   *   FALSE if copy was not successful, or
   *   file ID if copy was successful.
   */
  private function copyTheFile($file_managed) {
    $result = FALSE;
    $uri = is_object($file_managed) ? $file_managed->uri : $file_managed;
    $file_name = str_replace('public://', '', $uri);
    $file_name = str_replace('private://', '', $file_name);
    $uri = 'public://d7/';
    // ********* This is the substitute code:
    if (strstr($file_managed->uri, 'public://images')) {
      $file = getcwd() . $this->getd7PublicFolder() . $file_name;

    }
    else {
      $file = getcwd() . $this->getd7PrivateFolder() . $file_name;
      $uri = $uri . "priv/";
    }
    $this->output()->writeln("file: " . $file);
    $this->output()->writeln("uri: " . $uri . $file_name);
    $data = @file_get_contents($file);

    if ($data && $file = \Drupal::service('file.repository')
      ->writeData($data, $uri . $file_name, FILE_EXISTS_OVERWRITE)) {
      $result = $file->id();
    }
    else {
      $a = 1;
    }
    unset($data);
    return $result;
  }

  /**
   * Get the file's content.
   *
   * @param string $file_url
   *   The URL of the file.
   *
   * @return mixed|null
   *   The content of the file.
   */
  private function getTheFile($file_url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $file_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $result = curl_exec($ch);
    curl_close($ch);
    if (preg_match('/<error>/i', $result)) {
      return NULL;
    }
    else {
      return $result;
    }
  }

}
