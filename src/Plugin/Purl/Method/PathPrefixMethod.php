<?php

namespace Drupal\purl\Plugin\Purl\Method;

use Drupal\purl\Annotation\PurlMethod;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @PurlMethod(
 *     id="path_prefix"
 * )
 */
class PathPrefixMethod implements MethodInterface, RequestAlteringInterface, OutboundAlteringInterface
{
    public function contains(Request $request, $identifier)
    {
        $uri = $request->getRequestUri();
        return strpos($uri, '/' . $identifier) === 0;
    }

    public function alterRequest(Request $request, $identifier)
    {
        $uri = $request->getRequestUri();
        $newPath = substr($uri, strlen($identifier) + 1);
        $request->server->set('REQUEST_URI', $newPath);
    }

    public function alterOutbound($path, $modifier, &$options = null, Request $request = null)
    {
        return '/' . $modifier . $path;
    }
}
