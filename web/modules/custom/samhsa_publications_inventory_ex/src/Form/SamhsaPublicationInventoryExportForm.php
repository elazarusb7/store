<?php

namespace Drupal\samhsa_publications_inventory_ex\Form;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

/**
 * This form will include a number of batch operations
 * that relate to dealing with Article nodes.
 */
class SamhsaPublicationInventoryExportForm extends FormBase {

  protected $entity_type_manager;

  /**
   * The construct and create methods for dependency injection
   * to bring in the services needed for the Batch operations.
   */
  public function __construct(EntityTypeManager $entity_type_manager) {
    $this->entity_type_manager = $entity_type_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'),);
  }

  /**
   * Returns the machine name of the form.
   */
  public function getFormId() {
    return 'article_batch_form';
  }

  /**
   * Adds a submit button which will call the submit handler
   * to run the batch operation.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Genarate'),
    ];

    return $form;
  }

  /**
   * Runs the batch operation to add a tag to each article node.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $download_folder = 'public://publication_inventory_export';
    $file_name = 'publication_inventory_export' . date('m-d-Y') . '.csv';
    $header = $this->csv_header();
    // Append header values.
    if (!file_exists($download_folder)) {
      if (!mkdir($download_folder, 0777, TRUE) && !is_dir($download_folder)) {
        throw new \RuntimeException(sprintf('Directory "%s" was not created', $download_folder));
      }
    }

    $fp = fopen($download_folder . '/' . $file_name, 'w');
    fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
    fwrite($fp, implode(",", $header) . "\n");
    fclose($fp);

    // Define the Operations variable
    $operations = [];

    for ($i = 0; $i < 5; $i++) {
      $operations[] = [
        '\Drupal\samhsa_publications_inventory_ex\Form\SamhsaPublicationInventoryExportForm::process_batch',
        [$i, 2],
      ];
    }

    $batch = [
      'title' => $this->t('Publications Inventory Download'),
      'operations' => $operations,
      'finished' => '\Drupal\samhsa_publications_inventory_ex\Form\SamhsaPublicationInventoryExportForm::process_finished_batch',
    ];
    batch_set($batch);
  }

  public static function process_batch($ids, $total, &$context) {
    $download_folder = 'public://publication_inventory_export';
    $file_name = 'publication_inventory_export' . date('m-d-Y') . '.csv';
    $results = ['test'];
    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = $total;
    }
    $fwp = fopen($download_folder . '/' . $file_name, 'a');
    if (!empty($results)) {
      fwrite($fwp, "vicky" . "\n");
      $context['sandbox']['progress']++;
    }
    fclose($fwp);
    //check if batch is finished and update progress
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = ($context['sandbox']['progress'] >= $context['sandbox']['max']);
    }
  }

  /**
   * @param $success
   * @param $results
   * @param $operations
   */
  public static function process_finished_batch($success, $results, $operations) {
    if ($success) {
      \Drupal::messenger()
        ->addStatus('Publication Inventory was exported successfully');
      $uri = 'public://';
      $path = \Drupal::service('file_url_generator')->generateAbsoluteString($uri) . 'publication_inventory_export/' . 'publication_inventory_export' . date('m-d-Y') . '.csv';
      $url = Url::fromUri($path);
      \Drupal::messenger()
        ->addStatus(t('Download <a href = "@here">here</a>', ["@here" => $url->toUriString()]));
    }
    else {
      \Drupal::messenger()->addError("An error occured!");
    }
  }

  /**
   * @return string[]
   */
  function csv_header() {
    return [
      'GOVT PUB NUMBER',
      'TITLE',
      'FORMAT',
      'OFFICE OR CENTER',
      'PROGRAM/CAMPAIGN',
      'PUBLICATION DATE',
      'ITEM OWNER',
      'AVAILABLE QTY',
      'ALLOCATED QTY',
      'ON HAND QTY',
      'MAX ORDER QTY',
      'PUBLISHED STATUS',
      'STOCK STATUS',
      'DISPLAY MODE',
      'PALLETS',
      'LOCATION OF PRODUCT',
    ];
  }

}
