<?php
/**
 * Created by PhpStorm.
 * User: bez
 * Date: 2016-02-02
 * Time: 1:02 PM
 */

namespace Drupal\purl\RouteProcessor;


use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\RouteProcessor\OutboundRouteProcessorInterface;
use Drupal\purl\Event\ModifierMatchedEvent;
use Drupal\purl\Plugin\MethodPluginManager;
use Drupal\purl\Plugin\Purl\Method\OutboundRouteAlteringInterface;
use Drupal\purl\PurlEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Route;

class PurlContextOutboundRouteProcessor implements OutboundRouteProcessorInterface, EventSubscriberInterface
{
    /**
     * @var MethodPluginManager
     */
    private $manager;

    /**
     * @var ModifierMatchedEvent[]
     */
    private $events = array();

    public function __construct(MethodPluginManager $manager)
    {
        $this->manager = $manager;
    }

    public function processOutbound($route_name, Route $route, array &$parameters, BubbleableMetadata $bubbleable_metadata = NULL)
    {
        foreach ($this->events as $event) {
            $method = $event->getMethod();
            if ($method instanceof OutboundRouteAlteringInterface) {
                $path = $method->alterOutboundRoute($route_name, $event->getModifier(), $route, $parameters, $bubbleable_metadata);
            }
        }
    }

    public function onModifierMatched(ModifierMatchedEvent $event)
    {
        $this->events[] = $event;
    }

    public static function getSubscribedEvents()
    {
        return [
            PurlEvents::MODIFIER_MATCHED => array('onModifierMatched', 300),
        ];
    }
}
