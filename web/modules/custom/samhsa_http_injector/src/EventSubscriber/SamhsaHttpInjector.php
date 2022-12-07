<?php
/**
 * @file
 * Contains \Drupal\samhsa_http_injector\EventSubscriber\SamhsaHttpInjector.
 */

namespace Drupal\samhsa_http_injector\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event Subscriber SamhsaHttpInjector.
 */
class SamhsaHttpInjector implements EventSubscriberInterface {

  public function onRespond(ResponseEvent $event) {
    // get the response to the event
    $response = $event->getResponse();
    // get an instance of routeMatch
    $r = \Drupal::routeMatch();
    // is the object being request in the processed Symfony route a node?
    $node = $r->getParameter('node');
    if (is_object($node)) {
      // create a date of the correct format
      $date = gmdate(DATE_RFC1123, $node->getChangedTime());
      // inject the date as a response header - which will be cached as well
      $response->headers->set('Last-Modified', $date);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['onRespond'];
    return $events;
  }

}
