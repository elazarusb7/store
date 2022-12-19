<?php

namespace Drupal\samhsa_pep_clone_order;

/**
 * Interface PepUtilityFunctionsInterface.
 *
 * @package Drupal\samhsa_pep_clone_order
 */
interface PepCloneOrderFunctionsInterface {

  /**
   *
   * @param $order
   *   The Commerce Order.
   *
   * @return order_id string
   *   The new cloned commerce order id.
   */
  public function cloneOrder($order = NULL);

}
