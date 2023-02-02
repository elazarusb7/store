<?php

namespace Drupal\samhsa_pep_clone_order\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'clone_order_button_block' block.
 *
 * @Block(
 *   id = "clone_order_button_block",
 *   admin_label = @Translation("Clone Order Button block"),
 *   category = @Translation("Clone order button block")
 * )
 */
class CloneOrderButtonBLock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $current_route = \Drupal::routeMatch();

    if (($current_route->getParameters()->get('commerce_order'))) {
      $entity = $current_route->getParameters()->get('commerce_order');
      $state = $entity->getState()->getValue()['value'];

      $order_states_clone = \Drupal::config('samhsa_pep_clone_order.settings')
        ->get('order_states');

      // Only show "clone order" button for order in specified order states saved in the config page.
      if (in_array($state, $order_states_clone) && $order_states_clone[$state]) {
        $form = \Drupal::formBuilder()->getForm('Drupal\samhsa_pep_clone_order\Form\CloneOrderForm');
      }
    }
    return $form;
  }

}
