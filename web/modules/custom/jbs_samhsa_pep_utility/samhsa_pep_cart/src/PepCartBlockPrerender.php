<?php

namespace Drupal\samhsa_pep_cart;

use Drupal\Core\Render\Element\RenderCallbackInterface;
use Drupal\Core\Url;

/**
 * Class PepCartBlockPrerender.
 */
class PepCartBlockPrerender implements RenderCallbackInterface {

  /**
   *
   */
  public static function _pep_cart_block_cart_prerender($build) {
    $all_carts = \Drupal::service('commerce_cart.cart_provider')
      ->getCarts();
    if (is_array($all_carts) && count($all_carts) == 1) {
      $all_carts = array_shift($all_carts);
      $order_id = $all_carts->id();

      $links = [];
      $links[] = [
        '#prefix' => "<span class = \"cart-link\">",
        '#suffix' => "</span>",
        '#type' => 'link',
        '#title' => t('Shopping Cart'),
        '#url' => Url::fromRoute('commerce_cart.page'),
      ];
      if (isset($order_id) && $order_id > 0) {
        $links[] = [
          '#prefix' => "<span class = \"checkout-link\">",
          '#suffix' => "</span>",
          '#type' => 'link',
          '#title' => t('Checkout'),
          '#url' => Url::fromRoute('commerce_checkout.form', ['commerce_order' => $order_id, 'step' => 'order_information']),
        ];
      }
      $build['content']['#links'] = $links;
    }
    return $build;
  }

}
