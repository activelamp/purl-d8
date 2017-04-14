<?php

namespace Drupal\purl\Event;

use Drupal\Core\Render\Element\Url;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\purl\PurlEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\purl\MatchedModifiers;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\Response;
use Drupal\redirect\Exception\RedirectLoopException;

/**
 * Event subscriber for redirecting nodes that do not need to keep context.
 */
class PurlNodeContextRoutes implements EventSubscriberInterface {

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityStorage;

  protected $routeMatch;

  /**
   * @var MatchedModifiers
   */
  protected $matchedModifiers;

  /**
   * PurlNodeContextRoutes constructor.
   *
   * @param EntityTypeManagerInterface $entity_type_manager
   * @param RouteMatchInterface        $route_match
   * @param MatchedModifiers           $matchedModifiers
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RouteMatchInterface $route_match, MatchedModifiers $matchedModifiers) {
    $this->entityStorage = $entity_type_manager->getStorage('node_type');
    $this->routeMatch = $route_match;
    $this->matchedModifiers = $matchedModifiers;
  }

  /**
   * Checks if a node's type requires a redirect.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The event to process.
   */
  public function purlCheckNodeContext(GetResponseEvent $event, $eventName, EventDispatcherInterface $dispatcher_interface) {
    $route_options = $this->routeMatch->getRouteObject()->getOptions();
    $isAdminRoute = array_key_exists('_admin_route', $route_options) && $route_options['_admin_route'];

    if (!$isAdminRoute
      && $matched = $this->matchedModifiers->getMatched()
      && $entity = $this->routeMatch->getParameter('node')
    ) {
      $node_type = $this->entityStorage->load($entity->bundle());
      $purl_settings = $node_type->getThirdPartySettings('purl');

      if ($entity->isPublished()) {
        if (!isset($purl_settings['keep_context']) || !$purl_settings['keep_context']) {
          $url = \Drupal\Core\Url::fromRoute($this->routeMatch->getRouteName(), $this->routeMatch->getRawParameters()->all(), [
            'host' => Settings::get('purl_base_domain'),
            'absolute' => TRUE
          ]);
          try {
            $redirect_response = new TrustedRedirectResponse($url->toString());
            $redirect_response->getCacheableMetadata()->setCacheMaxAge(0);
            $modifiers = $event->getRequest()->attributes->get('purl.matched_modifiers', []);
            $new_event = new ExitedContextEvent($event->getRequest(), $redirect_response, $this->routeMatch, $modifiers);
            $dispatcher_interface->dispatch(PurlEvents::EXITED_CONTEXT, $new_event);
            $event->setResponse($new_event->getResponse());
            return;
          }
          catch (RedirectLoopException $e) {
            \Drupal::logger('redirect')->warning($e->getMessage());
            $response = new Response();
            $response->setStatusCode(503);
            $response->setContent('Service unavailable');
            $event->setResponse($response);
            return;
          }
        }
      } else {
        if (!isset($purl_settings['keep_context']) || !$purl_settings['keep_context']) {
          drupal_set_message(
            $entity->label() . ' is currently unpublished. This node is set to remove the context, anonymous users will be redirected to the main base domain.',
            'status',
            true
          );
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    // Run after \Drupal\system\EventSubscriber\AdminRouteSubscriber.
    $events[KernelEvents::REQUEST][] = ['purlCheckNodeContext', -21];
    return $events;
  }
}
