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
class PathPrefixMethod implements MethodInterface, RequestAlteringInterface
{
    public function contains(Request $request, $modifier)
    {
        $uri = $request->getRequestUri();
        return $this->pathContains($modifier, $uri);
    }

    private function pathContains($modifier, $path)
    {
        return strpos($path, '/' . $modifier) === 0;
    }

    public function alterRequest(Request $request, $identifier)
    {
        $uri = $request->getRequestUri();
        $newPath = substr($uri, strlen($identifier) + 1);
        $request->server->set('REQUEST_URI', $newPath);
    }

    public function enterContext($modifier, $path, array &$options)
    {
        return '/' . $modifier . $path;
    }

    public function exitContext($modifier, $path, array &$options)
    {
        if (!$this->pathContains($modifier, $path)) {
           return null;
        }

        return substr($path, 0, strlen($modifier) + 1);
    }
}
