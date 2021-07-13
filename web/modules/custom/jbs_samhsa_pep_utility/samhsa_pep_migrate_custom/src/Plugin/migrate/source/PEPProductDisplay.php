<?php

namespace Drupal\samhsa_pep_migrate_custom\Plugin\migrate\source;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\commerce_store\Resolver\DefaultStoreResolver;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\d7\FieldableEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;
use PDO;

/**
 * DGets Commerce 1 commerce_line_item data from database.
 *
 * @MigrateSource(
 *   id = "pep_product_display",
 *   source_module = "commerce_product"
 * )
 */
class PEPProductDisplay extends FieldableEntity {

  /**
   * The default store resolver.
   *
   * @var \Drupal\commerce_store\Resolver\DefaultStoreResolver
   */
  protected $defaultStoreResolver;
  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, StateInterface $state, EntityTypeManagerInterface $entity_type_manager, DefaultStoreResolver $default_store_resolver) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $state, $entity_type_manager);
    $this->defaultStoreResolver = $default_store_resolver;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('state'),
      $container->get('entity.manager'),
      $container->get('commerce_store.default_store_resolver')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'nid' => t('Product (variation) ID'),
      'title' => t('Title'),
      'type' => t('Type'),
      'uid' => t('Owner'),
      'status' => t('Status'),
      'created' => t('Created'),
      'changed' => t('Changes'),
      'field_name' => t('Field name for variations'),
      'variations_field' => t('Value of the product reference field'),
      'body/format' => t('Format of body'),
      'body/value' => t('Full text of body'),
      'body/summary' => t('Summary of body'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['nid']['type'] = 'integer';
    $ids['nid']['alias'] = 'n';

    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('node', 'n');
    $query->join('field_data_field_samhsa_product_publication', 'pp','(pp.entity_id = n.nid)');
    $query->leftJoin('field_config_instance', 'fci', '(n.type = fci.bundle)');
    $query->leftJoin('field_config', 'fc', '(fc.id = fci.field_id)');
    $query->condition('fc.type', 'commerce_product_reference');
    $query->fields('n', [
      'nid',
      'title',
      'type',
      'uid',
      'status',
      'created',
      'changed',
    ]);
    $query->fields('pp', [
      'field_samhsa_product_publication_product_id',
    ]);
    $query->fields('fc', ['field_name']);
    //\Drupal::logger('samhsa_pep_migrate_custom')->notice($query);
    if (isset($this->configuration['product_type'])) {
      $query->condition('n.type', $this->configuration['product_type']);
    }
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row)
  {
      $product_id = $row->getSourceProperty('field_samhsa_product_publication_product_id');

      $default_store = $this->defaultStoreResolver->resolve();
      if ($default_store) {
          $row->setDestinationProperty('stores', ['target_id' => $default_store->id()]);
      } else {
          throw new MigrateException('You must have a store saved in order to import products.');
      }

      $variations_field_name = $row->getSourceProperty('field_name');
      // Get Field API field values.
      $nid = $row->getSourceProperty('nid');
      $vid = $row->getSourceProperty('vid');
      $type = 'samhsa_publications';
      $bundle = 'samhsa_product_display';
      foreach (array_keys($this->getFields('node', $row->getSourceProperty('type'))) as $field) {
          // If this is the product reference field, map it to `variations_field`
          // since it does not have a standardized name.
          if ($field == $variations_field_name) {
              $row->setSourceProperty('variations_field', $this->getFieldValues('node', $variations_field_name, $nid, $vid));
          } else {
              $row->setSourceProperty($field, $this->getFieldValues('node', $field, $nid, $vid));
          }
      }

      // Body (description).
      //$result = $this->getDatabase()->query('
      $query = 'SELECT
        body_value,
        body_summary,
        body_format
      FROM
        field_data_body
      WHERE
        entity_id = :nid AND bundle = :bundle';
      //\Drupal::logger('samhsa_pep_migrate_custom')->notice($query);

      $result = $this->getDatabase()->query($query, array(':nid' => $nid, ':bundle' => $bundle));

      //$body_files_and_images = array();
      foreach ($result as $record) {
          //$body_files_and_images += \Drupal::service('set_migrating_values')->extractImagesAndFiles($record->body_value);
          //$row->setSourceProperty('body_value', \Drupal::service('set_migrating_values')->convertReferencesAndSources($record->body_value, $product_id, $bundle));
          //$row->setSourceProperty('body_summary', \Drupal::service('set_migrating_values')->convertReferencesAndSources($record->body_summary, $product_id, $bundle));
          //$row->setSourceProperty('body_format', \Drupal::service('set_migrating_values')->getTextFormat($record->body_format));
          $row->setSourceProperty('body_value', $record->body_value);
          $row->setSourceProperty('body_summary', $record->body_summary);
          $row->setSourceProperty('body_format', $record->body_format);

      }

      // publication_date.
      $query = 'SELECT field_publication_date_value
      FROM
      field_data_field_publication_date
      WHERE entity_id = :entity_id AND bundle = :bundle';
      //\Drupal::logger('samhsa_pep_migrate_custom')->notice($query);
      $result = $this->getDatabase()->query($query, array(':entity_id' => $product_id, ':bundle' => $type));
      $values = [];
      //\Drupal::logger('samhsa_pep_migrate_custom')->notice($type);
      foreach ($result as $record) {
          $values[] = date('Y-m-d', strtotime($record->field_publication_date_value));

      }
      $row->setSourceProperty('field_publication_date', $values);

      // field_you_maybe_interested.
      $query = 'SELECT field_you_maybe_interested_value
      FROM
      field_data_field_you_maybe_interested
      WHERE entity_id = :entity_id AND bundle = :bundle';
      //\Drupal::logger('samhsa_pep_migrate_custom')->notice($query);
      $result = $this->getDatabase()->query($query, array(':entity_id' => $nid, ':bundle' => $bundle));
      $values = [];
      //\Drupal::logger('samhsa_pep_migrate_custom')->notice($type);

      foreach ($result as $record) {
          $values[] = $record->field_you_maybe_interested_value;
          //\Drupal::logger('samhsa_pep_migrate_custom')->notice($record->field_you_maybe_interested_value);
      }
      if(count($values) == 0) {
          $values[] = 'auto';
      }
      $row->setSourceProperty('field_related_products', $values);

      // field_samhsa_promo_link
      $components = array(
          'fields_prefix' => 'field_samhsa_promo_link',
          'table' => 'field_data_field_samhsa_promo_link',
          'property' => 'field_samhsa_promo_link',
      );
      \Drupal::service('set_migrating_values')->link($this, $row, $components);

      // Related Document Link.
      $components = array(
          'fields_prefix' => 'field_related_document_link',
          'table' => 'field_data_field_related_document_link',
          'property' => 'field_related_document_link',
      );
      \Drupal::service('set_migrating_values')->link($this, $row, $components);

      //field_data_field_max_purchase
      $query = 'SELECT field_max_purchase_value
      FROM
      field_data_field_max_purchase
      WHERE entity_id = :entity_id AND bundle = :bundle';
      //\Drupal::logger('samhsa_pep_migrate_custom')->notice($query);
      $result = $this->getDatabase()->query($query, array(':entity_id' => $product_id,':bundle' => $type));
      $values = [];
      //\Drupal::logger('samhsa_pep_migrate_custom')->notice($type);
      foreach ($result as $record) {
          $values[] = $record->field_max_purchase_value;
          //\Drupal::logger('samhsa_pep_migrate_custom')->notice($record->field_max_purchase_value);
      }
      $row->setSourceProperty('field_data_field_max_purchase', $values);

      //field_samhsa_stock_status
      $query = 'SELECT field_samhsa_stock_status_value
      FROM
      field_data_field_samhsa_stock_status
      WHERE entity_id = :entity_id AND bundle = :bundle';
      //\Drupal::logger('samhsa_pep_migrate_custom')->notice($query);
      $result = $this->getDatabase()->query($query, array(':entity_id' => $product_id,':bundle' => $type));
      $values = [];

      foreach ($result as $record) {
          //\Drupal::logger('samhsa_pep_migrate_custom')->notice('product_id:'. $product_id);

          $hasDownloads = $this->getDownloadsCount($product_id);
          //\Drupal::logger('samhsa_pep_migrate_custom')->notice('hasdownloads:'. $product_id . ":" . $hasDownloads);
          $stock = $this->getProductStock($product_id);
          //\Drupal::logger('samhsa_pep_migrate_custom')->notice('stock:'. $product_id . ":" . $stock);

          switch ($record->field_samhsa_stock_status_value)
          {
              case  'ELECTRONIC ONLY':
                  $value = 'download_only';
                  break;
              case  'IN STOCK':
              case  'OUT OF STOCK':
                  //if stock > 0 and has downloads -> download_order
                  //if stock > 0 and no downloads -> order_only
                  if($hasDownloads)
                  {
                      $value = 'download_order';
                  } elseif($stock > 0) {
                      $value = 'order_only';
                  }
                  break;
          }
          $values[] = $value;
          //\Drupal::logger('samhsa_pep_migrate_custom')->notice($value);
      }
      $row->setSourceProperty('field_samhsa_stock_status', $values);

      //field_data_field_related_products
      /*$components = array(
          'field' => 'field_related_products_target_id',
          'table' => 'field_data_field_related_products',
          'property' => 'field_also_be_interested_manual',
      );
      \Drupal::service('set_migrating_values')->entityReference($this, $row, $components);*/

      $query = 'SELECT field_related_products_target_id
      FROM
      field_data_field_related_products
      WHERE entity_id = :entity_id AND bundle = :bundle';
      $result = $this->getDatabase()->query($query, array(':entity_id' => $nid,':bundle' => $bundle));
      $values = [];
      foreach ($result as $record) {
          $values[] = $record->field_related_products_target_id;
          //\Drupal::logger('samhsa_pep_migrate_custom')->notice($nid ." / ".$record->field_related_products_target_id);
      }
      $row->setSourceProperty('field_also_be_interested_manual', $values);


      //field_imported_language_value
      $languages = array();
      $languages['CA'] = 'KM';
      $languages['CH'] = 'ZH';
      $languages['EC'] = 'ZH';
      $languages['KH'] = 'KM';
      $languages['PJ'] = 'PA';
      $languages['CA'] = 'KM';
      $languages['SP'] = 'ES';
      $languages['VE'] = 'VI';

      $query = 'SELECT field_imported_language_value
      FROM
      field_data_field_imported_language
      WHERE entity_id = :entity_id AND bundle = :bundle';
      //\Drupal::logger('samhsa_pep_migrate_custom')->notice($query);
      $result = $this->getDatabase()->query($query, array(':entity_id' => $product_id,':bundle' => $type));
      $values = [];
      foreach ($result as $record) {
          if(empty($value = $record->field_imported_language_value)){
              $value = 'EN';
          } else {
              if (array_key_exists($record->field_imported_language_value, $languages)) {
                  $value = $languages[$record->field_imported_language_value];
              } else {
                  $value = $record->field_imported_language_value;
              }
          }
        $values[] = $value;
        //\Drupal::logger('samhsa_pep_migrate_custom')->notice('Language: ' .$product_id . " / " .$record->field_imported_language_value);
      }

      $row->setSourceProperty('imported_language_value', $values);

      //Terms Migration
      // field_substances.
      $components = array(
          'field' => 'field_taxo_substances_tid',
          'table' => 'field_data_field_taxo_substances',
          'property' => 'substances',
      );
      \Drupal::service('set_migrating_values')->termReference($this, $row, $components);

      //field_issues_conditions_and_diso: issues_conditions
      $components = array(
          'field' => 'field_issues_con_disorders_tid',
          'table' => 'field_data_field_issues_con_disorders',
          'property' => 'issues_conditions',
      );
      \Drupal::service('set_migrating_values')->termReference($this, $row, $components);

      //field_professional_and_research_: professional_and_research
      $components = array(
          'field' => 'field_prof_research_topics_tid',
          'table' => 'field_data_field_prof_research_topics',
          'property' => 'professional_and_research',
      );
      \Drupal::service('set_migrating_values')->termReference($this, $row, $components);

      //field_treatment_prevention_and_r: treatment_prevention
      $components = array(
          'field' => 'field_treatmt_prevent_recovery_tid',
          'table' => 'field_data_field_treatmt_prevent_recovery',
          'property' => 'treatment_prevention',
      );
      \Drupal::service('set_migrating_values')->termReference($this, $row, $components);

      //field_taxo_audience: audience
      $components = array(
          'field' => 'field_taxo_audience_tid',
          'table' => 'field_data_field_taxo_audience',
          'property' => 'audience',
      );
      \Drupal::service('set_migrating_values')->termReference($this, $row, $components);

      //field_series: series
      $components = array(
          'field' => 'field_series_tid',
          'table' => 'field_data_field_series',
          'property' => 'series',
      );
      \Drupal::service('set_migrating_values')->termReference($this, $row, $components);

      //field_population_group: population_group
      $components = array(
          'field' => 'field_taxo_pop_group_tid',
          'table' => 'field_data_field_taxo_pop_group',
          'property' => 'population_group',
      );
      \Drupal::service('set_migrating_values')->termReference($this, $row, $components);

      //field_format: format
      $components = array(
          'field' => 'field_taxo_format_tid',
          'table' => 'field_data_field_taxo_format',
          'property' => 'format',
      );
      \Drupal::service('set_migrating_values')->termReference($this, $row, $components);

      //field_location: location
      $components = array(
          'field' => 'field_taxo_location_tid',
          'table' => 'field_data_field_taxo_location',
          'property' => 'location',
      );
      \Drupal::service('set_migrating_values')->termReference($this, $row, $components);

      //field_tags: tags
      $components = array(
          'field' => 'field_taxo_tags_tid',
          'table' => 'field_data_field_taxo_tags',
          'property' => 'tags',
      );
      \Drupal::service('set_migrating_values')->termReference($this, $row, $components);

      // field_thumbnail.
      $components = array(
          'fields_prefix' => 'field_product_image',
          'table' => 'field_data_field_product_image',
          'property' => 'thumbnail',
          'idfield' => 'nid'
      );
      \Drupal::service('set_migrating_values')->images($this, $row, $components);

      // field_data_field_samhsa_digital_download.
      $components = array(
          'fields_prefix' => 'field_samhsa_digital_download',
          'table' => 'field_data_field_samhsa_digital_download',
          'property' => 'samhsa_digital_download',
          'idfield' => 'field_samhsa_product_publication_product_id',
          'display_name_tbl' => 'field_data_field_samhsa_display_name',
          'display_name_field' => 'field_samhsa_display_name_value',
      );
      \Drupal::service('set_migrating_values')->files_with_display_name($this, $row, $components);

      return parent::prepareRow($row);


  }

  /**
   * Return number of downloads per product.
   */
   public function getDownloadsCount($product_id) {
      $query = 'SELECT entity_id
      FROM
      field_data_field_samhsa_digital_download
      WHERE entity_id = :entity_id';
      $result = $this->getDatabase()->query($query, array(':entity_id' => $product_id))->fetchAll();

      return count($result);
  }

  /**
   * Return product stock.
   */
   public function getProductStock($product_id) {
      $query = 'SELECT commerce_stock_value
      FROM
      field_data_commerce_stock
      WHERE entity_id = :entity_id';
      $result = $this->getDatabase()->query($query, array(':entity_id' => $product_id))->fetchAll();

      return $result[0]->commerce_stock_value;
  }
}

