<?php

namespace Drupal\samhsa_pep_password\EventSubscriber;

use Drupal;
use Drupal\Core\Url;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\user\Entity\User;

/**
 * Enforces password reset functionality.
 */
class SamhsaPepPasswordEventSubscriber implements EventSubscriberInterface {

  /**
   * Current path getter because paths > routes for users.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $pathStack;

  /**
   * Current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * Initialize method.
   *
   * @param \Drupal\Core\Path\CurrentPathStack $pathStack
   *   The current path.
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   The current user account.
   *
   *   arguments: ['@path.current', '@current_user'].
   */
  public function __construct(CurrentPathStack $pathStack, AccountProxyInterface $account) {
    $this->pathStack = $pathStack;
    $this->account = $account;
  }

  /**
   * Event callback to look for users expired password.
   */
  public function checkForUserPasswordExpiration(GetResponseEvent $event, $eventName, ContainerAwareEventDispatcher $dispatcher) {
    /** @var \Symfony\Component\HttpFoundation\Request */
    $request = $event->getRequest();
    $path = $this->pathStack->getPath($request);
    $url = Url::fromUserInput($path);
    $route_name = ($url->isRouted() ? $url->getRouteName() : 'not routed');

    $conf = Drupal::config('samhsa_pep_password.settings');
    if ($conf->get('lifetime_max_enforce')) {
      $account = Drupal::currentUser();
      // There needs to be an explicit check for non-anonymous or else
      // this will be tripped and a forced redirect will occur.
      if ($account->id() > 0) {
        /** @var \Drupal\user\UserInterface $user */
        $user = User::load($account->id());
        $ignore_route = in_array($route_name, [
          'entity.user.edit_form',
          'system.ajax',
          'user.logout',
          'admin_toolbar_tools.flush',
          'samhsa_pep_notification.form',
        ]);
        $is_ajax = $request->headers->get('X_REQUESTED_WITH') == 'XMLHttpRequest';
        $user_expired = FALSE;
        if ($user->get('field_password_expiration')) {
          $user_expired = $user->get('field_password_expiration')->value;
        }

        // @todo Consider excluding admins here.
        if ($user_expired && !$ignore_route && !$is_ajax) {
          $url = new Url('entity.user.edit_form', ['user' => $user->id()]);
          $url = $url->setAbsolute(TRUE)->toString();
          $url2 = new Url('entity.user.edit_form', ['user' => $user->id()]);
          $url2 = $url2->setAbsolute(FALSE)->toString();
          /** @var Symfony\Component\HttpFoundation\RedirectResponse */
          $redirect = new RedirectResponse($url2);
          $event->setResponse($redirect);
          Drupal::messenger()
            ->addMessage(t('Your password has expired.  You must change it to continue.'), 'error');
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // @todo Evaluate if there is a better place to add this check.
    $events[KernelEvents::REQUEST][] = ['checkForUserPasswordExpiration', 50];
    return $events;
  }

}
