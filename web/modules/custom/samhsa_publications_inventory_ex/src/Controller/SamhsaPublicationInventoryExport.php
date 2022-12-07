<?php

namespace Drupal\samhsa_publications_inventory_ex\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class SamhsaPublicationInventoryExport.
 *
 * @package Drupal\my_module\Controller
 */
class SamhsaPublicationInventoryExport extends ControllerBase implements ContainerInjectionInterface {

  /**
   * An instance of the entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'));
  }

  /**
   * Export a CSV of data.
   */
  public function build() {
    $handle = fopen('php://temp', 'w+');
    $header = [
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
    // Add the header as the first line of the CSV.
    fputcsv($handle, $header);

    $result = Drupal::database()->query("SELECT commerce_product_variation_field_data.sku AS sku, commerce_product_variation_field_data.title AS title, commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_format.field_format_target_id AS format, commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_office_center.field_office_center_value AS office_center, commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_campaign.field_campaign_value AS campaign, commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_publication_date.field_publication_date_value AS publication_date, commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_product_owner_name.field_product_owner_name_value AS owner_name, commerce_product_variation__field_available_quantity.field_available_quantity_value AS commerce_product_variation__field_available_quantity_field_a, commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_qty_max_order.field_qty_max_order_value AS commerce_product_field_data_commerce_product_variation_field_9, commerce_product_variation_field_data.variation_id AS variation_id, commerce_product_field_data_commerce_product_variation_field_data.status AS commerce_product_field_data_commerce_product_variation_field_10, commerce_product_variation__field_pallet_location.field_pallet_location_value AS commerce_product_variation__field_pallet_location_field_pall, SUM(commerce_order_item_commerce_product_variation_field_data.quantity) AS commerce_order_item_commerce_product_variation_field_data_qu, MIN(commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_pep_product_type.field_pep_product_type_value) AS commerce_product_field_data_commerce_product_variation_field_5, MIN(commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_pep_product_type.delta) AS commerce_product_field_data_commerce_product_variation_field_6, MIN(commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_pep_product_type.langcode) AS commerce_product_field_data_commerce_product_variation_field_7, MIN(commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_pep_product_type.bundle) AS commerce_product_field_data_commerce_product_variation_field_8, MIN(commerce_product_variation_field_data.variation_id) AS variation_id_1, MIN(commerce_product_field_data_commerce_product_variation_field_data.product_id) AS commerce_product_field_data_commerce_product_variation_field_11, MIN(commerce_order_item_commerce_product_variation_field_data.order_item_id) AS commerce_order_item_commerce_product_variation_field_data_or
FROM {commerce_product_variation_field_data} commerce_product_variation_field_data
LEFT JOIN {commerce_product_field_data} commerce_product_field_data_commerce_product_variation_field_data ON commerce_product_variation_field_data.product_id = commerce_product_field_data_commerce_product_variation_field_data.product_id
LEFT JOIN {commerce_order_item} commerce_order_item_commerce_product_variation_field_data ON commerce_product_variation_field_data.variation_id = commerce_order_item_commerce_product_variation_field_data.purchased_entity
LEFT JOIN {commerce_product__stores} commerce_product_field_data_commerce_product_variation_field_data__commerce_product__stores ON commerce_product_field_data_commerce_product_variation_field_data.product_id = commerce_product_field_data_commerce_product_variation_field_data__commerce_product__stores.entity_id AND commerce_product_field_data_commerce_product_variation_field_data__commerce_product__stores.deleted = '0'
LEFT JOIN {commerce_product__field_format} commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_format ON commerce_product_field_data_commerce_product_variation_field_data.product_id = commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_format.entity_id AND commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_format.deleted = '0'
LEFT JOIN {commerce_product__field_office_center} commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_office_center ON commerce_product_field_data_commerce_product_variation_field_data.product_id = commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_office_center.entity_id AND commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_office_center.deleted = '0'
LEFT JOIN {commerce_product__field_campaign} commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_campaign ON commerce_product_field_data_commerce_product_variation_field_data.product_id = commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_campaign.entity_id AND commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_campaign.deleted = '0'
LEFT JOIN {commerce_product__field_publication_date} commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_publication_date ON commerce_product_field_data_commerce_product_variation_field_data.product_id = commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_publication_date.entity_id AND commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_publication_date.deleted = '0'
LEFT JOIN {commerce_product__field_product_owner_name} commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_product_owner_name ON commerce_product_field_data_commerce_product_variation_field_data.product_id = commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_product_owner_name.entity_id AND commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_product_owner_name.deleted = '0'
LEFT JOIN {commerce_product_variation__0f4a4b1f10} commerce_product_variation__field_available_quantity ON commerce_product_variation_field_data.variation_id = commerce_product_variation__field_available_quantity.entity_id AND commerce_product_variation__field_available_quantity.deleted = '0'
LEFT JOIN {commerce_product__field_pep_product_type} commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_pep_product_type ON commerce_product_field_data_commerce_product_variation_field_data.product_id = commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_pep_product_type.entity_id AND commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_pep_product_type.deleted = '0'
LEFT JOIN {commerce_product__field_qty_max_order} commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_qty_max_order ON commerce_product_field_data_commerce_product_variation_field_data.product_id = commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_qty_max_order.entity_id AND commerce_product_field_data_commerce_product_variation_field_data__commerce_product__field_qty_max_order.deleted = '0'
LEFT JOIN {commerce_product_variation__9ec3c7b4e5} commerce_product_variation__field_pallet_location ON commerce_product_variation_field_data.variation_id = commerce_product_variation__field_pallet_location.entity_id AND commerce_product_variation__field_pallet_location.deleted = '0'
WHERE (commerce_product_variation_field_data.status = '1') AND (commerce_product_field_data_commerce_product_variation_field_data__commerce_product__stores.stores_target_id = '1')
GROUP BY sku, title, format, office_center, campaign, publication_date, owner_name, commerce_product_variation__field_available_quantity_field_a, commerce_product_field_data_commerce_product_variation_field_9, commerce_product_variation_field_data.variation_id, commerce_product_field_data_commerce_product_variation_field_10, commerce_product_variation__field_pallet_location_field_pall");
    foreach ($result as $res) {
      $data = [
        $res->sku,
        $res->title,
        $res->format,
        $res->office_center,
        $res->campaign,
        $res->publication_date,
        $res->owner_name,
        $res->commerce_order_item_commerce_product_variation_field_data_qu,
        $res->commerce_product_field_data_commerce_product_variation_field_9,
        $res->commerce_product_field_data_commerce_product_variation_field_10,
        $res->commerce_product_variation__field_pallet_location_field_pall,
        $res->commerce_order_item_commerce_product_variation_field_data_qu,
        $res->commerce_product_field_data_commerce_product_variation_field_5,
        $res->commerce_product_field_data_commerce_product_variation_field_6,
        $res->commerce_product_field_data_commerce_product_variation_field_7,
        $res->commerce_product_field_data_commerce_product_variation_field_8,
        $res->variation_id_1,
        $res->commerce_product_field_data_commerce_product_variation_field_11,
        $res->commerce_order_item_commerce_product_variation_field_data_or,
      ];

      // Add the data we exported to the next line of the CSV>.
      fputcsv($handle, array_values($data));
    }
    // Reset where we are in the CSV.
    rewind($handle);

    // Retrieve the data from the file handler.
    $csv_data = stream_get_contents($handle);

    // Close the file handler since we don't need it anymore.  We are not storing
    // this file anywhere in the filesystem.
    fclose($handle);

    // This is the "magic" part of the code.  Once the data is built, we can
    // return it as a response.
    $response = new Response();

    // By setting these 2 header options, the browser will see the URL
    // used by this Controller to return a CSV file called "article-report.csv".
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="samhsa_publications_inventory.csv"');

    // This line physically adds the CSV data we created.
    $response->setContent($csv_data);

    return $response;
  }

}
