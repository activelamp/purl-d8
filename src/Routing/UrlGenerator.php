<?php

namespace Drupal\purl\Routing;

use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\RouteProcessor\OutboundRouteProcessorInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Routing\UrlGenerator as UrlGeneratorBase;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RequestContext;


// @TODO: Consider decorating @url_generator.non_bubbling instead.

class UrlGenerator implements UrlGeneratorInterface
{

    protected $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param RequestContext $context
     */
    public function setContext(RequestContext $context)
    {
        $this->urlGenerator->setContext($context);
    }

    /**
     * @param string|\Symfony\Component\Routing\Route $name
     * @param array $parameters
     * @param array $options
     * @param bool $collect_bubbleable_metadata
     * @return \Drupal\Core\GeneratedUrl|string
     */
    public function generateFromRoute($name, $parameters = array(), $options = array(), $collect_bubbleable_metadata = FALSE)
    {
        $hostOverride = null;
        $originalHost = null;

        if (isset($options['host']) && strlen((string) $options['host']) > 0) {
            $hostOverride = $options['host'];
            $originalHost = $this->getContext()->getHost();
            $this->getContext()->setHost($hostOverride);
        }

        $result = $this->urlGenerator->generateFromRoute($name, $parameters, $options, $collect_bubbleable_metadata);

        // Reset the original host in request context.
        if ($hostOverride) {
            $this->getContext()->setHost($originalHost);
        }

        return $result;
    }

    /**
     * Gets the request context.
     *
     * @return RequestContext The context
     */
    public function getContext()
    {
        return $this->urlGenerator->getContext();
    }

    /**
     * @param string $name
     * @param array $parameters
     * @param bool|string $referenceType
     * @return string
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        return $this->urlGenerator->generate($name, $parameters, $referenceType);
    }

    /**
     * @param string|\Symfony\Component\Routing\Route $name
     * @param array $parameters
     * @return string
     */
    public function getPathFromRoute($name, $parameters = array())
    {
        return $this->urlGenerator->getPathFromRoute($name, $parameters);
    }

    /**
     * @param mixed $name
     * @return bool
     */
    public function supports($name)
    {
        return $this->urlGenerator->supports($name);
    }

    /**
     * @param mixed $name
     * @param array $parameters
     * @return string
     */
    public function getRouteDebugMessage($name, array $parameters = array())
    {
        return $this->urlGenerator->getRouteDebugMessage($name, $parameters);
    }
}
