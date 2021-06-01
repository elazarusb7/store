<?php

/**
 * @file
 * Contains \Drupal\samhsa_pep_utility\PepStockUtility.
 */

namespace Drupal\samhsa_pep_stock;
use Drupal\Core\Entity\EntityInterface;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
/**
 * Interface PepStockUtilityInterface.
 *
 * @package Drupal\samhsa_pep_stock
 */
interface PepStockUtilityInterface {
    /**
     *
     * @param array $pallets
     *   array of pallets/locations to be removed.
     * @param int $variation_id
     *   int variation  which location(s) needs to be removed.
     * @param string $update_to
     *   string to update location to.
     *
     */
    public function releasePallets(array $pallets, $variation_id, $update_to = '');

    /**
     * Gets the last transaction id for a given location and purchasable entity.
     *
     * @param int $location_id
     *   Location id.
     *  @param int $transaction_id
     *   Transaction id.
     * @param \Drupal\commerce\PurchasableEntityInterface $entity
     *   The purchasable entity.
     *
     * @return data serialized array
     *   The comments, user, other data.
     */
    public function getTransactionData($location_id, PurchasableEntityInterface $entity, $transaction_id);

    /**
     * Gets the last transaction id and data order item entity.
     *
     * @param \Drupal\commerce_order\Entity\OrderItemInterface $entity
     *   The purchasable entity.
     *
     * @return array
     *   The last transaction id, data.
     */
    public function getOrderItemTransactionInfo(OrderItemInterface $entity);

    /**
     * Gets the last transaction id for a given location and purchasable entity.
     *
     * @param \Drupal\commerce\PurchasableEntityInterface $entity
     *   The purchasable entity.
     *
     * @param int $order_id
     *
     * @return int
     *   The last location stock transaction id.
     */
    public function getLocationStockTransactionLatest(PurchasableEntityInterface $entity, $order_id = null);

    /**
     * Gets the stock for a given purchasable entity.
     *
     * @param \Drupal\commerce\PurchasableEntityInterface $entity
     *   The purchasable entity.
     *
     * @return int
     *   The Stock.
     */
    public function getStock(PurchasableEntityInterface $entity);

    /**
     *
     * @param $variation_id
     *   The Commerce Product variation id.
     *
     * @return array
     *  ['entity_id']
     */
    public function getAllocated($variation_id);

    /**
     *
     * @param $variation_id
     *   The Commerce Product variation id.
     *
     * @return multi-dimentional array
     *  ['entity_id','LOCATION_ZONE','name']
     */
    function lookupPublicationPallets($variation_id);

    /**
     * Update pallets locations field for a given purchasable entity.
     * @param $variation_id
     *   The Commerce Product variation id.
     *
     * @param \Drupal\commerce\PurchasableEntityInterface $entity
     *   The purchasable entity.
     *
     */
    function updateProductPallets($variation_id, PurchasableEntityInterface $variation);
}
