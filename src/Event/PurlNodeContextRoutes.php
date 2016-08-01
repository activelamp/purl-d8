<?php

namespace Drupal\purl\Event;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\ParamConverter\ParamConverterManagerInterface;
use Drupal\Core\Render\Element\Url;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RoutingEvents;
use Drupal\Core\Routing\RouteBuildEvent;
use Drupal\purl\MatchedModifiers;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;
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
   * Constructs a new PageManagerRoutes.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tags_invalidator
   *   The cache tags invalidator.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RouteMatchInterface $route_match, MatchedModifiers $matchedModifiers) {
    $this->entityStorage = $entity_type_manager->getStorage('node_type');
    $this->routeMatch = $route_match;
    $this->matchedModifiers = $matchedModifiers;
  }

  /**
   * Applies parameter converters to route parameters.
   *
   * @param \Drupal\Core\Routing\RouteBuildEvent $event
   *   The event to process.
   */
  public function purlCheckNodeContext(GetResponseEvent $event) {
    $route_options = $this->routeMatch->getRouteObject()->getOptions();
    $isAdminRoute = array_key_exists('_admin_route', $route_options) && $route_options['_admin_route'];

    if (!$isAdminRoute && $this->matchedModifiers->getMatched() && $entity = $this->routeMatch->getParameter('node')) {
      $node_type = $this->entityStorage->load($entity->bundle());
      $purl_settings = $node_type->getThirdPartySettings('purl');

      if (!isset($purl_settings['keep_context']) || !$purl_settings['keep_context']) {
        $url = \Drupal\Core\Url::fromRoute($this->routeMatch->getRouteName(), $this->routeMatch->getRawParameters()->all(), [
          'purl_context' => false,
          'host' => Settings::get('purl_base_domain'),
          'absolute' => TRUE
        ]);
        try {
          $event->setResponse(new TrustedRedirectResponse($url->toString()));
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
