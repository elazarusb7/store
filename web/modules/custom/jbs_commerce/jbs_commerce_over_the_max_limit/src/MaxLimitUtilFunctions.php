<?php

/**
 * @file
 * Contains \Drupal\jbs_commerce_over_the_max_limit\MaxLimitUtilFunctions.
 */

namespace Drupal\jbs_commerce_over_the_max_limit;

use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Render\Markup;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Core\Entity\EntityInterface;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;


/**
 * Class MaxLimitUtilFunctions.
 *
 * @package Drupal\jbs_commerce_over_the_max_limit
 */
class MaxLimitUtilFunctions implements MaxLimitUtilFunctionsInterface {
  /**
   * Constructor.
   */
  public function __construct() {
    $this->basePath = getcwd();
    $request = Request::createFromGlobals();
    $this->baseUrl = $request->getBaseUrl();
  }

  public function isOrderBulk(EntityInterface $order){
      $countBulkItems = 0;
      // Make sure it's a order entity.
      if ($order instanceof OrderInterface) {
          $items = $order->getItems();
          foreach($items as $key => $item){
              $qty = $item->getQuantity();
              $entity = $item->getPurchasedEntity();
              if (isset($entity)) {
                $product_id = $entity->get('product_id')->getValue()[0]['target_id'];
                $product = Product::load($product_id);
                //ksm("qty: " . $qty);
                $maxlimit_machine_name_config = \Drupal::config('jbs_commerce_over_the_max_limit.settings')
                  ->get('maxlimit_element', 'field_qty_max_order');
                if (isset($maxlimit_machine_name_config) && !empty($maxlimit_machine_name_config)) {
                  $max_limit_field = $maxlimit_machine_name_config;
                }
                else {
                  $max_limit_field = 'field_qty_max_order';
                }
                if (!empty($product) && !empty($max_limit_field)) {
                  if ($product->get($max_limit_field)) {
                    $max = $product->get($max_limit_field)->getValue()[0]['value'];
                    if ($qty > $max) {
                      $countBulkItems++;
                    }
                  }
                }
              }
          }
      }

      return $countBulkItems > 0 ? TRUE : FALSE;
  }

    public function isItemOverTheMaxLimit(EntityInterface $item){
        $overTheLimit = FALSE;
        // Make sure it's a order entity.
        if ($item instanceof OrderItemInterface) {
                $qty = $item->getQuantity();
                $product_id = $item->getPurchasedEntity()->get('product_id')->getValue()[0]['target_id'];
                $product = Product::load($product_id);
                //ksm("qty: " . $qty);
                $maxlimit_machine_name_config = \Drupal::config('jbs_commerce_over_the_max_limit.settings')
                    ->get('maxlimit_element','field_qty_max_order');
                if(isset($maxlimit_machine_name_config) && !empty($maxlimit_machine_name_config)){
                    $max_limit_field = $maxlimit_machine_name_config;
                } else {
                    $max_limit_field = 'field_qty_max_order';
                }
                if (!empty($product) && !empty($max_limit_field)) {
                    if ($product->get($max_limit_field)) {
                        $max = $product->get($max_limit_field)->getValue()[0]['value'];
                        if ($qty > $max) {
                            $overTheLimit = TRUE;
                        }
                    }
                }
            }

        return $overTheLimit;
    }
}



