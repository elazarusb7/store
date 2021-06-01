<?php

/**
 * @file
 * Contains \Drupal\jbs_commerce_over_the_max_limit\MaxLimitUtilFunctionsInterface.
 */

namespace Drupal\jbs_commerce_over_the_max_limit;
use Drupal\Core\Entity\EntityInterface;

/**
 * Interface MaxLimitUtilFunctionsInterface.
 *
 * @package Drupal\jbs_commerce_over_the_max_limit
 */
interface MaxLimitUtilFunctionsInterface {
  /**
   *
   * @param $order
   *   Commerce Order.
   *
   * @return boolean true/false
   * check if order is bulk. At least one item in the order should be over the max limit
   */
  public function isOrderBulk(EntityInterface $order);

    /**
     *
     * @param $orderItem
     *   Commerce Order Item.
     *
     * @return boolean true/false
     * check if order item is over the max limit, return TRUE
     */
    public function isItemOverTheMaxLimit(EntityInterface $orderItem);
}
