<?php

/**
 * @file
 * Enables integration of our custom fields on the Commerce order form.
 *
 * Based loosely on https://docs.drupalcommerce.org/commerce2/developer-guide/checkout/create-custom-checkout-pane#example-5-use-the-entityformdisplay-class-for-pane-form-build-va
 */
namespace Drupal\samhsa_commerce_enhancements\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the payment process pane.
 *
 * @CommerceCheckoutPane(
 *   id = "special_request",
 *   label = @Translation("Special request"),
 *   default_step = "order_information",
 *   wrapper_element = "container",
 * )
 */
class SamhsaCommerceSpecialRequestFields extends CheckoutPaneBase {

  /**
   * @param array $pane_form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param array $complete_form
   *
   * @return array
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form): array {
    $form_display = EntityFormDisplay::collectRenderDisplay($this->order, 'checkout');
    $form_display->buildForm($this->order, $pane_form, $form_state);

    return $pane_form;
  }

  /**
   * @param array $pane_form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param array $complete_form
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $form_display = EntityFormDisplay::collectRenderDisplay($this->order, 'checkout');
    $form_display->extractFormValues($this->order, $pane_form, $form_state);
    $form_display->validateFormValues($this->order, $pane_form, $form_state);
  }

  /**
   * @param array $pane_form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param array $complete_form
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $form_display = EntityFormDisplay::collectRenderDisplay($this->order, 'checkout');
    $form_display->extractFormValues($this->order, $pane_form, $form_state);
  }

}
