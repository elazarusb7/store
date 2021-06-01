<?php
/**
* @file
* Contains \Drupal\samhsa_pep_shipping/Form\PepShippingSettingsForm.
*/

namespace Drupal\samhsa_pep_shipping\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class PepShippingSettingsForm extends ConfigFormBase {
    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'pep_shipping_admin_settings';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $elements = array('mg' => 'mg','g' => 'g', 'kg' => 'kg', 'oz' => 'oz', 'lb' => 'lb');
        $unit = $this->config('samhsa_pep_shipping.settings')
            ->get('unit');
        $form['unit'] = array(
            '#type' => 'select',
            '#title' => $this->t('Select Default Weight Unit'),
            '#default_value' => $unit,
            '#options' => $elements,
        );

        $elements = array('10' => '10','50' => '50','100' => '100','200' => '200', '300' => '300', '400' => '400', '500' => '500', '600' => '600');
        $bulk_number = $this->config('samhsa_pep_shipping.settings')
            ->get('bulk_number');
        $form['bulk_number'] = array(
            '#type' => 'select',
            '#title' => $this->t('Select weight in lb when order considered bulk'),
            '#default_value' => $bulk_number,
            '#options' => $elements,
        );

        $elements = array('1' => 'one','0' => 'Unlimited');
        $number_of_shipments = $this->config('samhsa_pep_shipping.settings')
            ->get('number_of_shipments');
        $form['number_of_shipments'] = array(
            '#type' => 'select',
            '#title' => $this->t('Allowed number of shipments'),
            '#default_value' => isset($number_of_shipments) ? $number_of_shipments : "1",
            '#options' => $elements,
        );

        $elements = array('1' => 'Yes','0' => 'No');
        $allow_shipment_deletion = $this->config('samhsa_pep_shipping.settings')
            ->get('allow_shipment_deletion');
        $form['allow_shipment_deletion'] = array(
            '#type' => 'select',
            '#title' => $this->t('Allowed Drupal administrator to delete shipments'),
            '#default_value' => isset($allow_shipment_deletion) ? $allow_shipment_deletion : "0",
            '#options' => $elements,
        );

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $values = $form_state->getValues();
        $this->config('samhsa_pep_shipping.settings')
            ->set('unit', isset($values['unit']) ? $values['unit'] : 'oz')
            ->set('bulk_number', isset($values['bulk_number']) ? $values['bulk_number'] : '500')
            ->set('number_of_shipments', isset($values['number_of_shipments']) ? $values['number_of_shipments'] : '1')
            ->set('allow_shipment_deletion', isset($values['allow_shipment_deletion']) ? $values['allow_shipment_deletion'] : '0')
            ->save();

        parent::submitForm($form, $form_state);
    }

    /**
    * {@inheritdoc}
    */
    protected function getEditableConfigNames() {
          return ['samhsa_pep_shipping.settings'];
    }

}