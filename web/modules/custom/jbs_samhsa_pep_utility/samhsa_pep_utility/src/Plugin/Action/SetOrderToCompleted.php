<?php

namespace Drupal\samhsa_pep_utility\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Component\Render\FormattableMarkup;

/**
* Action to set order status to Completed.
*
* @Action(
*   id = "samhsa_pep_utility_complete_order",
*   label = @Translation("Complete Order"),
*   type = "commerce_order",
*   confirm = TRUE,
*   requirements = {
*     "_permission" = "complete order",
*     "_custom_access" = TRUE,
*   },
* )
*/

class SetOrderToCompleted extends ViewsBulkOperationsActionBase {

    use StringTranslationTrait;

    /**
     * {@inheritdoc}
     */
    public function execute($entity = NULL) {
        // Do some processing..
        if ($entity->hasField('state')) {
            $entity->set('state', 'completed');
            if($entity->hasField('field_date_completed')) {
                $date = date('Y-m-d', time());
                $entity->set('field_date_completed', $date);
            }
            $entity->save();
            $logcomments = '';
            if($entity->hasField('field_log')){
                $logcomments = $entity->field_log->value;
            }
            $log_storage = \Drupal::entityTypeManager()->getStorage('commerce_log');
            $log = $log_storage->generate($entity, 'commerce_order_state_updated', ['message' => "Order Completed: ". $logcomments])->save();

            /*//send email notification
            $to = $entity->getEmail(); //Fetch owner email
            $ordernumber = $entity->id();
            $subject = "Order Shipped.";

            $message = new FormattableMarkup("Order # %ordernumber<br /><br />
                        Dear customer,<br /><br />

                        Your order has been shipped. You should receive the materials in 10-12 days<br />
                        
                        If you have any questions about your order, please use the Order # provided above when contacting us<br />
                        
                        Note: If you ordered products over the Max Limit, only the authorized quantity has been shipped.<br />
                        
                        Regards,<br />
                        
                        SAMHSA Fulfillment Team<br />

                        If you have questions or comments regarding your order, please send an email to 
                        <a href = 'mailto:order@samhsa.hhs.gov'>order@samhsa.hhs.gov</a> with your order number. 
                        For all other questions or comments, please contact <a href = 'mailto:SAMHSAInfo@SAMHSA.hhs.gov'>SAMHSAInfo@SAMHSA.hhs.gov</a>.",
                array('%ordernumber' => $ordernumber));

            $this->messenger()->addStatus("Order Shipped");
            send_mail($entity, 'samhsa_pep', 'order_state', $subject, $message, $ordernumber, $to );
            */

        }
        // Don't return anything for a default completion message, otherwise return translatable markup.
        return $this->t('Order status changed to Shipped');
    }

    /**
     * {@inheritdoc}
     */
    public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
        if ($object->getEntityType() === 'commerce_order') {
            $access = $object->access('update', $account, TRUE)
                ->andIf($object->status->access('edit', $account, TRUE));
            return $return_as_object ? $access : $access->isAllowed();
        }

        // Other entity types may have different
        // access methods and properties.
        return TRUE;
    }

}