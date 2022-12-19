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
 *   id = "footer_address_block",
 *   admin_label = @Translation("Footer Address"),
 *   category = "SAMHSA Theme Blocks",
 * )
 */
class FooterAddressBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      // '#markup' => $this->t('This is a simple block!'),
      '#theme' => 'footer_address',
      '#title' => 'Footer Address',
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
