<?php

namespace Drupal\samhsa_theme_block_pep8\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "mobile_hamburger_block",
 *   admin_label = @Translation("Mobile Hamburger"),
 *   category = "SAMHSA Theme Blocks",
 * )
 */
class MobileHamburgerBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'mobile_hamburger',
      '#title' => 'Mobile Hamburger',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['my_block_settings'] = $form_state->getValue('my_block_settings');
  }

}
