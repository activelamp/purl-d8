<?php

namespace Drupal\purl\Plugin\Purl\Method;

use Drupal\purl\Annotation\PurlMethod;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @PurlMethod(
 *     id="subdomain",
 *     name="Subdomain"
 * )
 */
class SubdomainMethod implements MethodInterface, ContainerAwareInterface
{

    use ContainerAwareTrait;

    public function contains(Request $request, $identifier)
    {

        $baseHost = $this->getBaseHost();

        if (!$baseHost) {
            return false;
        }

        $host = $request->getHost();

        if ($host === $this->getBaseHost()) {
            return false;
        }

        return strpos($request->getHost(), $identifier . '.') === 0;
    }

    private function getBaseHost()
    {
        // Retrieve this from configuration or container paramater bag (maybe)
        return 'apa.dev';
    }
}
