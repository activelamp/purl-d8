<?php

namespace Drupal\purl\Plugin\Purl\Method;

use Drupal\purl\Annotation\PurlMethod;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;

/**
 * @PurlMethod(
 *     id="subdomain",
 *     name="Subdomain"
 * )
 */
class SubdomainMethod implements MethodInterface, ContainerAwareInterface, OutboundAlteringInterface
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
        // Retrieve this from configuration or container parameter bag (maybe)
        return 'apa.dev';
    }

    public function alterOutbound($path, $modifier, &$options = null, Request $request = null)
    {
        $baseHost = $this->getBaseHost();

        if (!$baseHost) {
            return $path;
        }

        $options['absolute'] = true;
        $options['host'] = sprintf('%s.%s', $modifier, $this->getBaseHost());
        return $path;
    }
}
