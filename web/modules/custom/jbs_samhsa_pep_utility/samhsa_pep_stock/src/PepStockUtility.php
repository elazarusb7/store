<?php

/**
 * @file
 * Contains \Drupal\samhsa_pep_stock\PepStockUtility.
 */

namespace Drupal\samhsa_pep_stock;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Render\Markup;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Core\Entity\EntityInterface;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;

/**
 * Class PepStockUtility.
 *
 * @package Drupal\samhsa_pep_stock
 */
class PepStockUtility implements PepStockUtilityInterface {
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
    public function releasePallets(array $pallets, $variation_id, $update_to = '') {
        $table_name = 'commerce_stock_transaction';
        $field = 'location_zone';
        foreach ($pallets as $key => $pallet) {
            \Drupal::database()->update($table_name)
                ->condition('entity_id', $variation_id, '=')
                ->condition('location_zone', $pallet, '=')
                ->condition('transaction_type_id', 7, '!=')
                //->condition('transaction_type_id', 8, '!=')
                ->fields([
                    'location_zone' => $update_to,
                ])
                ->execute();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLastTransactionDataAndLocation(PurchasableEntityInterface $entity, $transaction_id) {
        //\Drupal::logger('transacrion id')->warning('<pre><code>' . $transaction_id . '</code></pre>');

        $query = \Drupal::database()->select('commerce_stock_transaction','t')
            ->fields('t',['data','location_id','id'])
            ->condition('id', $transaction_id)
            ->condition('entity_id', $entity->id())
            ->condition('entity_type', $entity->getEntityTypeId());

        $result = $query
            ->execute()
            ->fetch();
        $arrresult=[];
        if($result) {
            $arrresult[] = [
                'location_id' => $result->location_id,
                'data' => unserialize($result->data),
                'id' => $result->id,
            ];
        }
        //\Drupal::logger('arr result 1')->warning('<pre><code>' . print_r($arrresult, TRUE) . '</code></pre>');

        return $arrresult;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransactionData($location_id, PurchasableEntityInterface $entity, $transaction_id) {
        $query = \Drupal::database()->select('commerce_stock_transaction','t')
            //$query = \Drupal::database()->select('commerce_product_variation_field_data', 't');
            ->fields('t',['data'])
            ->condition('id', $transaction_id)
            ->condition('location_id', $location_id)
            ->condition('entity_id', $entity->id())
            ->condition('entity_type', $entity->getEntityTypeId());

        $result = $query
            ->execute()
            ->fetch();
        //ksm($result);
        return $result ? $result->data : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderItemTransactionInfo(OrderItemInterface $entity){
      $item = $entity;
      $variation = $item->getPurchasedEntity();
      $order_id = $item->getOrderId();
      $location_level_for_variation = $this->getLocationStockTransactionLatest($variation, $order_id);
      $data = $this->getLastTransactionDataAndLocation($variation, $location_level_for_variation);
      return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocationStockTransactionLatest(PurchasableEntityInterface $entity, $order_id = NULL) {
        $query = \Drupal::database()->select('commerce_stock_transaction')
            ->condition('entity_id', $entity->id())
            ->condition('entity_type', $entity->getEntityTypeId());
            if($order_id != NULL) {
                $query->condition('related_oid', $order_id);
            }

        $query->addExpression('MAX(id)', 'max_id');

        $result = $query
            ->execute()
            ->fetch();

        return $result && $result->max_id ? $result->max_id : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getStock(PurchasableEntityInterface $entity){
        //Get the Stock manager:
        $stockManager = \Drupal::service('commerce_stock.service_manager');
        $stock_service = $stockManager->getService($entity);
        $stock_checker = $stock_service->getStockChecker();
        $stock = $stock_checker->getTotalStockLevel($entity, $stock_checker->getLocationList(true));
        return $stock;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllocated($variation_id = NULL) {
      /*
       *  SELECT purchased_entity, SUM(quantity)
          FROM commerce_order_item
          LEFT JOIN commerce_order USING (order_id)
          WHERE state IN ('pending', 'pick_slips_generated', 'onhold')
          GROUP BY purchased_entity
      */
      $query = \Drupal::database()->select('commerce_order_item', 'oi')
        ->fields('oi', ['purchased_entity'])
        ->condition('co.state', ['pending', 'pick_slips_generated', 'onhold'], 'IN');
      $query->addExpression('SUM(quantity)', 'allocated');
      $query->leftjoin('commerce_order', 'co', 'co.order_id = oi.order_id');
      $query->groupBy('oi.purchased_entity');
      if (isset($variation_id)) {
        $query->condition('purchased_entity', $variation_id, '=');
        $qty = $query->execute()->fetchAllKeyed();
        if (isset($qty[$variation_id])) {
          return $qty[$variation_id];
        }
        else {
          return 0;
        }
      }
      return $query->execute()->fetchAllKeyed();
    }

    /**
     * {@inheritdoc}
     */
    function lookupPublicationPallets($variation_id) {
        // Get redirects for the given item's internal path or alias.
        $query = \Drupal::database()->select('commerce_stock_transaction', 't');
        $query->fields('t',['entity_id','LOCATION_ZONE', 'transaction_type_id']);
        $query->fields('tt',['name']);
        $query->condition('entity_id', $variation_id, '=');
        $query->condition('LOCATION_ZONE', NULL, 'IS NOT NULL' );
        $query->condition('LOCATION_ZONE', "", '!=' );
        $query->condition('name', "", '!=' );
        $query->condition('LOCATION_ZONE', "_none", '!=' );
        $query->condition('transaction_type_id', "7", '!=' );
        $query->addJoin('left','taxonomy_term_field_data','tt','t.LOCATION_ZONE=tt.tid');
        $query->distinct(true);
        $results = $query->orderBy('name', 'ASC')->execute();
        $results->allowRowCount = TRUE;

        // Get redirects if anything set for the given node.
        if ($results->rowCount()) {
            $results_list = [];

            while ($record = $results->fetchAssoc()) {
                $results_list[] = $record;
            }
            //ksm($results_list);
            return $results_list;
        }
    }

    /**
     * {@inheritdoc}
     */
    function updateProductPallets($variation_id, PurchasableEntityInterface $variation) {
        $pallets_used = lookupPublicationPallets($variation_id);
        $options = array();
        if(is_array($pallets_used)){
            foreach ($pallets_used as $row) {
                $options[] = $row['name'];
            }
        }
        $str = implode (", ", $options);
        $variation->set('field_pallet_location',$str);
        $variation->save();
    }
}
