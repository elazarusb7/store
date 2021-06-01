<?php

namespace Drupal\samhsa_pep_anonlogout\EventSubscriber;

//use Drupal\samhsa_pep_anonlogout\AnonlogoutManagerInterface;
use Drupal\commerce_order\Event\OrderEvents;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\commerce_cart\Event\CartEntityAddEvent;
use Drupal\commerce_cart\Event\CartEvents;
use Drupal\Core\Url;

/**
 * Defines autologout Subscriber.
 */
class AnonlogoutSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * settings
   */
  protected $settings;

  /**
   * Session manager.
   *
   * @var \Drupal\Core\Session\SessionManagerInterface
   */
  protected $sessionManager;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs an AnonlogoutSubscriber object.
   *
   * @param \Drupal\samhsa_pep_anonlogout\AnonlogoutManagerInterface $anonlogout
   *   The anonlogout manager service.
   */
  public function __construct(
    SessionManagerInterface $sessionManager,
    MessengerInterface      $messenger,
    TranslationInterface    $string_translation
  ) {
    $this->sessionManager    = $sessionManager;
    $this->messenger         = $messenger;
    $this->stringTranslation = $string_translation;
    $this->settings          = \Drupal::config('samhsa_pep_anonlogout.settings');
  }

  public function onRequest(GetResponseEvent $event)
  {
    // only run for anonymous sessions
    if (\Drupal::currentUser()->id() > 0 ) {
      return;
    }
    /* @var \Symfony\Component\HttpFoundation\Request */
    $request = \Drupal::request();
    $now = \Drupal::time()->getRequestTime();

    /* @var \Symfony\Component\HttpFoundation\Session\Session */
    $session = $request->getSession();
    $last    = $session->get('last', 2);
    if ($last == '') {
      $last = $now;
    }
    $lag = $now - $last;

    $timeout = $this->settings->get('timeout');
    if (!isset($timeout) || $timeout == 0) {
      $timeout = 60;
    }

    $redirect_url = $this->settings->get('redirect_url');
    if ($redirect_url == '') {
      $redirect_url = '/';
    }
    $clear_cart = FALSE;
    if ($lag > $timeout) {
      // over the limit so check for a cart
      if ($session->has('commerce_cart_orders')) {
        $commerce_cart_orders = $session->get('commerce_cart_orders');
        $cart_id = intval($commerce_cart_orders[0]);
        if ($cart_id) {
          // we have a cart so clear it
          $clear_cart = TRUE;
        }
      }
    }
    if ($clear_cart) {
      $session->clear();
      \Drupal::messenger()->addStatus(t('Your cart has expired due to inactivity'));
      if ($redirect_url != '') {
        $event->setResponse(new RedirectResponse($redirect_url));
      }
    }
    else if ($lag > 1) {
        // record the hit
        $session->set('last', $now);
        $path = $event->getRequest()->getPathInfo();

        //checking if url isRouted. Without checking,
        // it was throwing an error for anon user for invalid path.
        $url  = Url::fromUserInput($path);
        $route_name = ($url->isRouted() ? $url->getRouteName() : 'not routed');
        $route_chunks = explode('.', $route_name);
        //\Drupal::messenger()->addStatus("name: $route_name, chunks: " . print_r($route_chunks,true));
        if (isset($route_chunks[0])) {
            if ($route_chunks[0] == 'commerce_checkout' || $route_chunks[0] == 'commerce_cart') {
                $this->appendMessage();
            }
        }
    }
  }

  /**
   * Triggers message.
   *
   * @param \Drupal\commerce_cart\Event\CartEntityAddEvent $event
   *   The add to cart event.
   */
  public function appendAddToCartMessage(CartEntityAddEvent $event)
  {
      // only run for anonymous sessions
      if (\Drupal::currentUser()->id() > 0 ) {
          return;
      }
      $this->appendMessage();
  }

  /**
   * Displays the actual add to cart message.
   */
  private function appendMessage()
  {
    $msg = $this->settings->get('add_to_cart_message');
    if ($msg != '') {
      $this->messenger->addMessage($this->t($msg));
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents()
  {
    return [
      KernelEvents::REQUEST       => ['onRequest', 100],
      CartEvents::CART_ENTITY_ADD => ['appendAddToCartMessage',-1],
    ];
  }
}