<?php

/**
 * Subscriber for samhsa_pep_stock module routes.
 */
namespace Drupal\samhsa_pep_stock\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
* Listens to the dynamic route events.
*/
class RouteSubscriber extends RouteSubscriberBase
{
    /**
     * {@inheritdoc}
     */
    protected function alterRoutes(RouteCollection $collection)
    {
        if ($route = $collection->get('commerce_stock_ui.stock_transactions1')) {
            $route->setRequirement('_permission', 'perform stock transactions');// change permissions route.
        }
        if ($route = $collection->get('commerce_stock_ui.stock_transactions2')) {
            $route->setRequirement('_permission', 'perform stock transactions');// change permissions route.
        }
    }
}