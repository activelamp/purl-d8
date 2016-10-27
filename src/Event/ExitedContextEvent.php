<?php

namespace Drupal\purl\Event;

use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExitedContextEvent extends Event
{
  /**
   * @var Request
   */
  protected $request;

  protected $routeMatch;

  protected $matches;

  protected $response;

  /**
   * @param Request $request
   * @param string $providerKey
   * @param string $modifierKey
   * @param mixed $value
   */
  public function __construct(Request $request, Response $response, RouteMatchInterface $route_match, $matches)
  {
    $this->request = $request;
    $this->response = $response;
    $this->routeMatch = $route_match;
    $this->matches = $matches;
  }

  /**
   * @return Request
   */
  public function getRequest()
  {
    return $this->request;
  }

  /**
   * @return RouteMatchInterface
   */
  public function getRouteMatch()
  {
    return $this->routeMatch;
  }

  /**
   * @return
   */
  public function getMatches()
  {
    return $this->matches;
  }

  /**
   * @return Response
   */
  public function getResponse()
  {
    return $this->response;
  }

}
