<?php

namespace Drupal\purl\Routing;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Path\PathValidator;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\Routing\RouteProvider;
use Drupal\Core\State\StateInterface;
use Drupal\purl\ContextHelper;
use Drupal\purl\MatchedModifiers;
use Symfony\Component\HttpFoundation\Request;
use \Drupal\Core\Database\Connection;

/**
 * A Route Provider front-end for all Drupal-stored routes.
 */
class PurlRouteProvider extends RouteProvider {

  /**
   * The database connection from which to read route information.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The name of the SQL table from which to read the routes.
   *
   * @var string
   */
  protected $tableName;

  /**
   * The state.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * A cache of already-loaded routes, keyed by route name.
   *
   * @var \Symfony\Component\Routing\Route[]
   */
  protected $routes = array();

  /**
   * A cache of already-loaded serialized routes, keyed by route name.
   *
   * @var string[]
   */
  protected $serializedRoutes = [];

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The cache tag invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagInvalidator;

  /**
   * A path processor manager for resolving the system path.
   *
   * @var \Drupal\Core\PathProcessor\InboundPathProcessorInterface
   */
  protected $pathProcessor;

  protected $contextHelper;

  protected $matchedModifiers;

  /**
   * Cache ID prefix used to load routes.
   */
  const ROUTE_LOAD_CID_PREFIX = 'route_provider.route_load:';

  /**
   * Constructs a new PathMatcher.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   A database connection object.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   The cache backend.
   * @param \Drupal\Core\PathProcessor\InboundPathProcessorInterface $path_processor
   *   The path processor.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tag_invalidator
   *   The cache tag invalidator.
   * @param string $table
   *   (Optional) The table in the database to use for matching. Defaults to 'router'
   */
  public function __construct(
    Connection $connection,
    StateInterface $state,
    CurrentPathStack $current_path,
    CacheBackendInterface $cache_backend,
    InboundPathProcessorInterface $path_processor,
    CacheTagsInvalidatorInterface $cache_tag_invalidator,
    ContextHelper $contextHelper,
    MatchedModifiers $matchedModifiers
  ) {
    parent::__construct($connection, $state, $current_path, $cache_backend, $path_processor, $cache_tag_invalidator);
    $this->contextHelper = $contextHelper;
    $this->matchedModifiers = $matchedModifiers;
  }

  /**
   * @inheritdoc
   */
  public function getRouteCollectionForRequest(Request $request) {
    // Cache both the system path as well as route parameters and matching
    // routes.
    $matches = reset($this->matchedModifiers->getMatched());
    if ($matches) {
      $modifier = $matches->getModifier();
      $cid = 'route:' . '/' . $modifier . $request->getPathInfo() . ':' . $request->getQueryString();
    }
    else {
      $cid = 'route:' . $request->getPathInfo() . ':' . $request->getQueryString();
    }

    if ($cached = $this->cache->get($cid)) {
      $this->currentPath->setPath($cached->data['path'], $request);
      $request->query->replace($cached->data['query']);
      return $cached->data['routes'];
    }
    else {
      // Just trim on the right side.
      $path = $request->getPathInfo();
      $path = $path === '/' ? $path : rtrim($request->getPathInfo(), '/');
      $path = $this->pathProcessor->processInbound($path, $request);
      $this->currentPath->setPath($path, $request);
      // Incoming path processors may also set query parameters.
      $query_parameters = $request->query->all();
      $routes = $this->getRoutesByPath(rtrim($path, '/'));
      $cache_value = [
        'path' => $path,
        'query' => $query_parameters,
        'routes' => $routes,
      ];
      $this->cache->set($cid, $cache_value, CacheBackendInterface::CACHE_PERMANENT, ['route_match']);
      return $routes;
    }
  }
}
