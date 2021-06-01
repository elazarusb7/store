<?php
/**
 * Created by PhpStorm.
 * User: vlyalko
 * Date: 12/9/19
 * Time: 1:14 PM
 */

/**
 * @file
 * Contains \Drupal\samhsa_pep_clone_order\Form\CloneOrderForm.
 */
namespace Drupal\samhsa_pep_clone_order\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CloneOrderForm extends FormBase {
    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'clone_order_form';
    }
    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $form['actions']['#type'] = 'actions';
        $form['actions']['clone'] = array(
            '#type' => 'submit',
            '#value' => $this->t('Clone Order'),
            '#button_type' => 'danger',
        );

        return $form;
    }
    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $current_route = \Drupal::routeMatch();
        $comment ="";
        if(($current_route->getParameters()->get('commerce_order'))) {
            $entity = $current_route->getParameters()->get('commerce_order');
            $comment = 'This order cloned from order#: ' . $entity->id();
            $order_id = \Drupal::service('samhsa_pep_clone_order.pep_clone_order_functions')->cloneOrder($entity);
            if($order_id and is_numeric($order_id)){
                //redirect to the new order view page with the message
                /*$option = [
                    'query' => ['order_id' => $order->id()],
                ];
                $url = Url::fromUri('internal:/admin/commerce/orders/', $option);*/
                //drupal_flush_all_caches();
                \Drupal::service('cache.render')->invalidateAll();
                $url = Url::fromUri('internal:/admin/commerce/orders/'. $order_id);
                $response = new RedirectResponse($url->toString());
                $response->send();
                \Drupal::messenger()->addMessage(t($comment), 'status', TRUE);
                exit;
            }

        }
    }
}