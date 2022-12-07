<?php

namespace Drupal\samhsa_theme_block_pep8\Form;

use Drupal\block\Entity\Block;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure module settings.
 */
class SamhsaThemeBlockPEP8SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'samhsa_theme_block_pep8_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'samhsa_theme_block_pep8.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // @todo Error checking to check if these blocks still exist (they can be deleted by an admin user)
    // For now, changing to check if they exist before doing the status call, because/
    //  doing it all as it was originally, Block::load(block)->status() returns 500 if there is no block.
    $block = Block::load('samhsa_monobar');
    print '<h1>monobar=' . (is_object($block) ? 'true' : 'false') . '</h1>';
    $form['toggle_monobar'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mono Bar'),
      '#default_value' => is_object($block) ? $block->status() : FALSE,
    ];

    $block = Block::load('samhsa_header_social');
    $form['toggle_header_social'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Header Social'),
      '#default_value' => is_object($block) ? $block->status() : FALSE,
    ];

    $block = Block::load('samhsa_mobile_hamburger');
    $form['toggle_mobile_hamburger'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mobile Hamburger'),
      '#default_value' => is_object($block) ? $block->status() : FALSE,
    ];

    $block = Block::load('samhsa_share_buttons');
    $form['toggle_share_buttons'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Share Buttons'),
      '#default_value' => is_object($block) ? $block->status() : FALSE,
    ];

    $block = Block::load('samhsa_frontpage_motto');
    $form['toggle_frontpage_motto'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Frontpage Motto'),
      '#default_value' => is_object($block) ? $block->status() : FALSE,
    ];

    $block = Block::load('samhsa_jump_to_top');
    $form['toggle_jump_to_top'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Jump to Top'),
      '#default_value' => is_object($block) ? $block->status() : FALSE,
    ];

    $block = Block::load('samhsa_footer_nav_toggle');
    $form['toggle_footer_nav_toggle'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Footer Nav Toggle'),
      '#default_value' => is_object($block) ? $block->status() : FALSE,
    ];

    $block = Block::load('samhsa_footer_social');
    $form['toggle_footer_social'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Footer Social'),
      '#default_value' => is_object($block) ? $block->status() : FALSE,
    ];

    $block = Block::load('samhsa_footer_note');
    $form['toggle_footer_note'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Footer Note'),
      '#default_value' => is_object($block) ? $block->status() : FALSE,
    ];

    $block = Block::load('samhsa_newsletter_signup');
    $form['toggle_newsletter_signup'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Newsletter Signup'),
      '#default_value' => is_object($block) ? $block->status() : FALSE,
    ];

    $block = Block::load('samhsa_footer_banners');
    $form['toggle_footer_banners'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Footer Banners'),
      '#default_value' => is_object($block) ? $block->status() : FALSE,
    ];

    $block = Block::load('samhsa_language_assistance');
    $form['toggle_language_assistance'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Language Assistance'),
      '#default_value' => is_object($block) ? $block->status() : FALSE,
    ];

    $block = Block::load('samhsa_footer_address');
    $form['toggle_footer_address'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Footer Address'),
      '#default_value' => is_object($block) ? $block->status() : FALSE,
    ];

    $block = Block::load('samhsa_footer_logo');
    $form['toggle_footer_logo'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Footer Logo'),
      '#default_value' => is_object($block) ? $block->status() : FALSE,
    ];

    $block = Block::load('samhsa_footer_motto');
    $form['toggle_footer_motto'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Footer Motto'),
      '#default_value' => is_object($block) ? $block->status() : FALSE,
    ];

    $block = Block::load('samhsa_footer_motto_text_only');
    $form['toggle_footer_motto_text_only'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Footer Motto Text Only'),
      '#default_value' => is_object($block) ? $block->status() : FALSE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */

  /**
   * Changing logic to only call setStatus if $block contains an object,
   * otherwise 500 error.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $block = Block::load('samhsa_footer_address');
    if (is_object($block)) {
      $block->setStatus($form_state->getValue('toggle_footer_address'));
      $block->save();
    }

    $block = Block::load('samhsa_footer_banners');
    if (is_object($block)) {
      $block->setStatus($form_state->getValue('toggle_footer_banners'));
      $block->save();
    }

    $block = Block::load('samhsa_footer_logo');
    if (is_object($block)) {
      $block->setStatus($form_state->getValue('toggle_footer_logo'));
      $block->save();
    }

    $block = Block::load('samhsa_footer_motto_text_only');
    if (is_object($block)) {
      $block->setStatus($form_state->getValue('toggle_footer_motto_text_only'));
      $block->save();
    }

    $block = Block::load('samhsa_footer_motto');
    if (is_object($block)) {
      $block->setStatus($form_state->getValue('toggle_footer_motto'));
      $block->save();
    }

    $block = Block::load('samhsa_footer_note');
    if (is_object($block)) {
      $block->setStatus($form_state->getValue('toggle_footer_note'));
      $block->save();
    }

    $block = Block::load('samhsa_footer_nav_toggle');
    if (is_object($block)) {
      $block->setStatus($form_state->getValue('toggle_footer_nav_toggle'));
      $block->save();
    }

    $block = Block::load('samhsa_footer_social');
    if (is_object($block)) {
      $block->setStatus($form_state->getValue('toggle_footer_social'));
      $block->save();
    }

    $block = Block::load('samhsa_newsletter_signup');
    if (is_object($block)) {
      $block->setStatus($form_state->getValue('toggle_newsletter_signup'));
      $block->save();
    }

    $block = Block::load('samhsa_frontpage_motto');
    if (is_object($block)) {
      $block->setStatus($form_state->getValue('toggle_frontpage_motto'));
      $block->save();
    }

    $block = Block::load('samhsa_header_social');
    if (is_object($block)) {
      $block->setStatus($form_state->getValue('toggle_header_social'));
      $block->save();
    }

    $block = Block::load('samhsa_jump_to_top');
    if (is_object($block)) {
      $block->setStatus($form_state->getValue('toggle_jump_to_top'));
      $block->save();
    }

    $block = Block::load('samhsa_language_assistance');
    if (is_object($block)) {
      $block->setStatus($form_state->getValue('toggle_language_assistance'));
      $block->save();
    }

    $block = Block::load('samhsa_mobile_hamburger');
    if (is_object($block)) {
      $block->setStatus($form_state->getValue('toggle_mobile_hamburger'));
      $block->save();
    }

    $block = Block::load('samhsa_monobar');
    if (is_object($block)) {
      $block->setStatus($form_state->getValue('toggle_monobar'));
      $block->save();
    }

    $block = Block::load('samhsa_share_buttons');
    if (is_object($block)) {
      $block->setStatus($form_state->getValue('toggle_share_buttons'));
      $block->save();
    }

    parent::submitForm($form, $form_state);
  }

}
