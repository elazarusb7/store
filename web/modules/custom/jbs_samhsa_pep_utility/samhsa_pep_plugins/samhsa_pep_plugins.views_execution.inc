<?php
/**
* Created by PhpStorm.
* User: vlyalko
* Date: 2/8/2019
* Time: 1:25 PM
*/
use Drupal\views\ViewExecutable;

/**
 * Implements hook_views_pre_view().
 */
/*function samhsa_pep_stock_views_pre_view(ViewExecutable $view) {
    if ($view->id() == 'product_status_report' && $view->current_display == 'page_1') {
        // Show only three items. This assumes, that we already use a pager plugin,
        // that uses this option. Otherwise it wouldn't have any effect.
        //$view->setItemsPerPage(580);

        // Skip the first 5 items. Again, this assumes, that we already use a
        // pager plugin, that uses this option. Otherwise it wouldn't have any
        // effect.
        $view->setOffset(0);

        // If we need to change the pager type or other options of the pager, we
        // need to modify the pager option of the display handler.
        $pager = $view->display_handler->getOption('pager');

        // Change the pager plugin. Some will show a specified number of results.
        ksm($pager);
        $pager['type'] = 'none';
        // All options used by the plugin are available in $pager['options'].
        // If you want to change them, that's fine. You just need to know the keys.

        // Update pager option of the display handler.
        $view->display_handler->setOption('pager', $pager);
    }
}*/

/**
* Implements hook_views_pre_render().
*/
function samhsa_pep_plugins_views_pre_render(ViewExecutable $view) {

/*if ($view->id() == "publication_transaction_report"
    && ($view->current_display == 'page_2' || $view->current_display == 'data_export_1')) {
$total_stock = 0;
$total_allocated = 0;
$total_onhand = 0;

$count = 0;
foreach ($view->result as $key => $value) {
    $count++;
    //if($count == 1) {
        $qty = $value->commerce_order_item_commerce_product_variation_field_data_qu;
        $variation_id = $value->variation_id;
        $variation = $value->_entity;
        $stock = \Drupal::service('samhsa_pep_stock.pep_stock_utility')->getStock($variation);
        $total_stock += $stock;
        $total_allocated += $qty;
        $total_onhand += ($stock + $qty);
        //ksm($stock);
    //}
}

$view->attachment_after = [
'#markup' => '<table border = "0" class="table cols-0">
<tr>
<td><b>Totals:</b></td>
<td align="right">0</td>
<td >&nbsp;</td>
<td align="right"><b>0</b></td>
<td align="right">0</td>
<td align="right">0</td>
<td align="right">0</td>
<td align="right">' . $total_stock . '</td>
<td align="right">' . $total_allocated . '</td>
<td align="right">' . $total_onhand . '</td>
<td align="right">0</td>
<td align="right">0</td>
<td align="right">0</td>
<td align="right">0</td>
<td align="right">0</td>
<td align="right">0</td>
</tr>
</table>',
'#allowed_tags' => ['table','tr','td','b','strong']
];
}*/
}