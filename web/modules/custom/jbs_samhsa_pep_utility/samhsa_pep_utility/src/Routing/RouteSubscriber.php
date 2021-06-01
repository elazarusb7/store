<?php

/**
 * Subscriber for samhsa_pep_utility module routes.
 */
namespace Drupal\samhsa_pep_utility\Routing;

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
        // Change the route permissions for the order reassign form (admin/commerce/orders/{order_id}/reassign).
        if ($route = $collection->get('entity.commerce_order.reassign_form')) {
            //$route->setRequirement('_role', 'administrator');// change role route.
            //$collection->remove('entity.commerce_order.reassign_form');// remove route from collection
            $route->setRequirement('_permission', 'reassign orders');// change permissions route.

        }
    }
}