<?php

/**
 * @file
 * Contains samhsa_pep_order_states_workflow\samhsa_pep_order_states_workflow.views.inc..
 * Provide a custom views field data that isn't tied to any other module. */


/**
* Implements hook_views_data().
*/
function samhsa_pep_order_states_workflow_views_data() {
    $data['commerce_order']['order_states_view_field'] = [
        'title' => t('Order States - Custom'),
        'help' => t('Custom View field to display the Order states according to the role of th user'),
        'field' => [
            'id' => 'order_states_view_field',
        ],
    ];
    return $data;
}
