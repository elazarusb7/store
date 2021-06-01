<?php
/**
 * Created by PhpStorm.
 * User: vlyalko
 * Date: 10/29/19
 * Time: 2:03 PM
 */

namespace Drupal\samhsa_pep_justification_pane\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the coupons pane.
 *
 * @CommerceCheckoutPane(
 *   id = "checkout_review_justification",
 *   label = @Translation("Checkout Justification"),
 *   default_step = "review",
 * )
 */
class CommerceCheckoutReviewJustification extends CheckoutPaneBase implements CheckoutPaneInterface
{


    /**
     * {@inheritdoc}
     */
    public function __construct(array $configuration, $plugin_id, $plugin_definition, CheckoutFlowInterface $checkout_flow, EntityTypeManagerInterface $entity_type_manager)
    {
        parent::__construct($configuration, $plugin_id, $plugin_definition, $checkout_flow, $entity_type_manager);
    }

    /**
     * {@inheritdoc}
     */
    public function defaultConfiguration() {
        return parent::defaultConfiguration();
    }

    /**
     * {@inheritdoc}
     */
    public function buildConfigurationSummary() {
        //$summary = !empty($this->configuration['single_coupon']) ? $this->t('Single Coupon Usage on Order: Yes') : $this->t('Single Coupon Usage on Order: No');
        //return $summary;
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
        $form = parent::buildConfigurationForm($form, $form_state);

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
        parent::submitConfigurationForm($form, $form_state);

        if (!$form_state->getErrors()) {
            $values = $form_state->getValue($form['#parents']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
        $order = $this->order;
        if ($items_info = $this->justification_pane_get_items_info($order->id())) {

            $items_list = [];
            foreach ($items_info as $item) {
                $items_list[] = $item->title . '<br /><span>Pub ID: ' . $item->sku . '</span>';
            }

            $notice = t('The quantity requested for the product(s) below exceeds the maximum limit. Please tell us how you plan to use the product(s) below. We will let you know the quantity that is authorized.');
            $attributes = [
                'id' => 'justification-pane-list',
                'class' => 'justification-pane-items',
            ];

            $markup = "<p>" . $notice . "</p><ul>";
            foreach($items_list as $key => $value){
                $markup .= "<li id = 'justification-pane-list' class = 'justification-pane-items'>" . $value . "</li>";
            }
            $markup .= "</ul>";
            $pane_form['items_list'] = [
                '#type' => 'markup',
                '#markup' => $markup,
            ];

            $justification = isset($order->get('field_justification')->value) ? $order->get('field_justification')->value : '';
            $pane_form['justification_text'] = [
                '#type' => 'markup',
                '#title' => $this->t('Quantity Justification'),
                '#weight' => 10,
                '#markup' => '<div class="field field--label-above checkout-pane-checkout-review-justification--text">
    <div class="field__label">Justification</div>
              <div class="field__item">' . $justification . '</div>
          </div><p>Shipping may be delayed for orders that require approval. <a href="/help#help--ordering-online">Learn more about orders requiring approval.</a></p>',
            ];
        }
        return $pane_form;
    }

    /**
     * {@inheritdoc}
     */
    public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
        $values = $form_state->getValue($pane_form['#parents']);
        if(!empty($values['field_justification'])){
            $field_justification = $values['field_justification'];
        }
    }

    /**
     * Get information about the line items.
     *
     * @param integer $order_id
     *   Order ID.
     *
     * @return array
     *   Info about line items: title, quantity, max quantity allowed.
     */
    function justification_pane_get_items_info($order_id) {
    $query = \Drupal::database()->query("
    SELECT pvd.title AS title, pvd.sku AS sku, i.order_item_id AS order_item_id, i.quantity AS quantity, m.field_qty_max_order_value AS field_max_purchase_value
    FROM 
    commerce_order_item i
    INNER JOIN commerce_product__variations pv ON pv.variations_target_id = i.purchased_entity
    INNER JOIN commerce_product_variation_field_data pvd ON pv.variations_target_id = pvd.variation_id
    INNER JOIN commerce_product p ON pv.entity_id = p.product_id
    INNER JOIN commerce_product__field_qty_max_order m ON m.entity_id = p.product_id
    WHERE  
    (i.order_id = :order_id) AND 
    (i.quantity > m.field_qty_max_order_value) AND (m.bundle = 'samhsa_publication') 
    ORDER BY i.order_item_id ASC
  ", [':order_id' => $order_id]);
        $result = $query->fetchAll();
        return $result;
    }
}

