<?php

namespace Drupal\purl\Plugin\Purl\Method;

use Drupal\purl\Annotation\PurlMethod;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Site\Settings;

/**
 * @PurlMethod(
 *     id="subdomain",
 *     name="Subdomain"
 * )
 */
class SubdomainMethod implements MethodInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function contains(Request $request, $modifier)
    {
        $baseHost = $this->getBaseHost();

        if (!$baseHost) {
            return false;
        }

        $host = $request->getHost();

        if ($host === $this->getBaseHost()) {
            return false;
        }

        return $this->hostContainsModifier($modifier, $request->getHost());
    }

    private function hostContainsModifier($modifier, $host)
    {
        return strpos($host, $modifier . '.') === 0;
    }

    private function getBaseHost()
    {
        // Retrieve this from request context.
        return Settings::get('purl_base_domain');
    }

    public function enterContext($modifier, $path, array &$options)
    {
        $baseHost = $this->getBaseHost();

        if (!$baseHost) {
           return null;
        }

        $options['absolute'] = true;

        if ($this->hostContainsModifier($modifier, $baseHost)) {
            return null;
        }

        $options['host'] = sprintf('%s.%s', $modifier, $baseHost);

        return $path;
    }

    public function exitContext($modifier, $path, array &$options)
    {
        $baseHost = $this->getBaseHost();

        if (!$this->hostContainsModifier($modifier, $baseHost)) {
            return null;
        }

        // Strip out modifier sub-domain.
        $host = substr($baseHost, 0, strlen($modifier) + 1);

        $options['absolute'] = true;
        $options['host'] = $host;

        return $path;
    }
}
