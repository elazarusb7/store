<?php

namespace Drupal\samhsa_pep_notification\EventSubscriber;

use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\Core\Url;
use Drupal\Core\Database\Connection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Checks if the current user is required to accept an samhsa_pep_notification.
 */
class SamhsaPepNotificationSubscriber implements EventSubscriberInterface {
  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Current path getter because paths > routes for users.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $pathStack;

  /**
   * Session manager.
   *
   * @var \Drupal\Core\Session\SessionManagerInterface
   */
  protected $sessionManager;

  /**
   * Current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * Initialize method.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\Path\CurrentPathStack $pathStack
   *   The current path.
   * @param \Drupal\Core\Session\SessionManagerInterface $sessionManager
   *   The session manager service.
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   The current user account.
   *
   *   arguments: ['@samhsa_pep_notification.handler', '@path.current', '@session_manager', '@current_user'].
   */
  public function __construct(Connection $connection,
                              CurrentPathStack $pathStack,
                              SessionManagerInterface $sessionManager,
                              AccountProxyInterface $account
  ) {
    $this->connection = $connection;
    $this->pathStack = $pathStack;
    $this->sessionManager = $sessionManager;
    $this->account = $account;
  }

  /**
   * Check if the user needs to accept an samhsa_pep_notification.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The response event.
   */
  public function notificationCheck(GetResponseEvent $event) {
    // Users with the bypass samhsa_pep_notification permission are always excluded from any
    // samhsa_pep_notification.
    $request = $event->getRequest();
    $path = $this->pathStack->getPath($request);
    $url = Url::fromUserInput($path);
    if (!$url->isRouted()) {
      return;
    }
    $route_name = $url->getRouteName();
    $config     = \Drupal::config('samhsa_pep_notification.settings');
    $referer    = $request->server->get('HTTP_REFERER');
    $host       = $request->getSchemeAndHttpHost();
    $referer_uri = substr($referer ?? '', strlen($host));
    $ignore_routes = [
      'entity.user.edit_form',
      'system.ajax',
      'user.logout',
      'admin_toolbar_tools.flush',
      'samhsa_pep_notification.form',
    ];

    if (in_array($route_name, $ignore_routes)) {
      // don't go into an endless loop.
      return;
    }

    if (sizeof(array_intersect(\Drupal::currentUser()->getRoles(), $config->get('roles')))) {
      // We are in a role that requires acceptance.
      $query = $this->connection->select('samhsa_pep_notification_history', 'n')
        ->condition('n.uid', \Drupal::currentUser()->id())
        ->fields('n', ['sid', 'timestamp'])
        ->execute()
        ->fetchObject();

      if (is_object($query)) {
        // We have a previous acceptance.
        $age = intval(\Drupal::currentUser()->getLastAccessedTime()) - intval($query->timestamp);
        $one_day = 60 * 60 * 24;
        if ($age < $one_day && $query->sid == session_id()) {
          // Previous acceptance is valid.
          return;
        }
      }

      // if we get here, force new agreement
      // - redirect to the samhsa_pep_notification page.
      if ($referer_uri != $path && $referer_uri != '/user/login') {
        // Use tried to click away w/o accepting.
        \Drupal::messenger()->addMessage($config->get('failure'), 'warning');
      }
      $redirect_path = Url::fromRoute('samhsa_pep_notification.form')->toString();
      $event->setResponse(new RedirectResponse($redirect_path));
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    // Dynamic page cache will redirect to a cached page at priority 27.
    $events[KernelEvents::REQUEST][] = ['notificationCheck', 49];
    return $events;
  }

}
