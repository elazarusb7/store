<?php
/**
* @file
* Contains \Drupal\samhsa_pep_clone_order\Form\SamhsaPepCloneOrderSettingsForm.
*/

namespace Drupal\samhsa_pep_clone_order\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class SamhsaPepCloneOrderSettingsForm extends ConfigFormBase {
    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'samhsa_pep_clone_order';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $moduleHandler = \Drupal::service('module_handler');
        if ($moduleHandler->moduleExists('samhsa_pep_order_states_workflow')) {
            $available_states = \Drupal::service('samhsa_pep_order_states_workflow.workflow.helper')->getAllStates('samhsa_pep_order_states_workflow_fulfillment_processing');
            $value = $this->config('samhsa_pep_clone_order.settings')
                ->get('order_states');

            $selected = array_values($value);
            $form['order_states'] = array(
                '#title' => t('Order can be cloned when order is in the following state.'),
                '#type' => 'checkboxes',
                '#description' => t('Select the order states.'),
                '#options' => $available_states,
                '#default_value' => $selected,
            );
        }


        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $values = $form_state->getValues();
        $this->config('samhsa_pep_clone_order.settings')
            ->set('order_states', $values['order_states'])
            ->save();
        parent::submitForm($form, $form_state);
    }

    /**
    * {@inheritdoc}
    */
    protected function getEditableConfigNames() {
          return ['samhsa_pep_clone_order.settings'];
    }

}