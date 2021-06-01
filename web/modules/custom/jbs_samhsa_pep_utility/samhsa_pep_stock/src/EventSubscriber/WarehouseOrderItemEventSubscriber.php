<?php

namespace Drupal\samhsa_pep_stock\EventSubscriber;

use Drupal\views\Plugin\views\area\Entity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\commerce_cart\CartProviderInterface;
use Drupal\commerce_cart\Event\CartEntityAddEvent;
use Drupal\commerce_cart\Event\CartOrderItemUpdateEvent;
use Drupal\commerce_order\Event\OrderEvents;
use Drupal\commerce_order\Event\OrderItemEvent;
use Drupal\commerce_cart\Event\CartEvents;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_stock_local\LocalStockChecker;
use Drupal\Core\Link;


/**
 * Class WarehouseOrderItemEventSubscriber.
 */
class WarehouseOrderItemEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

    /**
     * The stock checker.
     *
     * @var \Drupal\commerce_stock\StockCheckInterface
     */
    protected $stockChecker;
    /**
     * The messenger.
     *
     * @var \Drupal\Core\Messenger\MessengerInterface
     */
    protected $messenger;

    /**
     * Constructs a new CartEventSubscriber object.
     *
     * @param \Drupal\Core\Messenger\MessengerInterface $messenger
     *   The messenger.
     * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
     *   The string translation.
     */
    public function __construct(MessengerInterface $messenger, TranslationInterface $string_translation) {
        $this->messenger = $messenger;
        $this->stringTranslation = $string_translation;
    }

  /**
   * This class is a placeholder for future implementation of the Event Subscriber
   * for tracking changes in the Order.
   */

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
      return [
          //OrderEvents::ORDER_ITEM_PRESAVE => 'checkIfStockNegative',
      ];
  }

    /**
     * Displays an add to cart message.
     *
     * @param \Drupal\commerce_order\Event\OrderItemEvent $event
     *   The add to cart event.
     */
    public function checkIfStockNegative(OrderItemEvent $event) {
        //ksm($event);
        //$order_item = $event->getOrderItem();
        //ksm($order_item);
        //$variation = $order_item->getPurchasedEntity();
        //ksm($order_item->getQuantity());
        //$original_order = $order_item->getOrder();
        //$original_items = $original_order->getItems();
        //ksm($original_items);
        //Get the Stock manager:
        //$stockManager = \Drupal::service('commerce_stock.service_manager');
        //$stock_service = $stockManager->getService($variation);
        //$stock_checker = $stock_service->getStockChecker();
        //$stock = $stock_checker->getTotalStockLevel($variation, $stock_checker->getLocationList(true));

        /*$stockManager = \Drupal::service('commerce_stock.service_manager');
        $stock_service = $stockManager->getService($variation);
        $stock_checker = $stock_service->getStockChecker();
        $locations = $stock_checker->getLocationList(true);
        //ksm($locations);
        $location_levels = [];
        foreach ($locations as $location) {
            $location_id = $location->getId();

            //commerce_stock.local_stock_checker
            $location_level = $stock_checker->getLocationStockTransactionLatest($location_id, $variation);

            $transactions_qty = $stock_checker->getLocationStockTransactionSum($location_id, $variation, $location_level['last_transaction'], $latest_txn);

            $location_levels[$location_id] = [
                'location_level' => $location_level,
            ];

            if (!empty(\Drupal::hasService('samhsa_pep_stock.pep_stock_utility'))) {
                $data = \Drupal::service('samhsa_pep_stock.pep_stock_utility')->getTransactionData(1, $variation, $location_id);
                ksm(unserialize($data));
                $location_levels[$location_id] = [
                    'data' => unserialize($data),
                ];
            }

            //$location_levels[$location_id] = $location_level;

        }
        ksm($location_levels);
        */
    }
}
