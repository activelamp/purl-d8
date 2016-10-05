<?php

namespace Drupal\purl\PathProcessor;

use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\purl\Event\ModifierMatchedEvent;
use Drupal\purl\Plugin\MethodPluginManager;
use Drupal\purl\Plugin\Purl\Method\OutboundAlteringInterface;
use Drupal\purl\PurlEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

class PurlContextOutboundPathProcessor implements OutboundPathProcessorInterface, EventSubscriberInterface
{
    /**
     * @var ModifierMatchedEvent[]
     */
    protected $events = array();

    protected $methodManager;

    public function __construct(MethodPluginManager $methodManager)
    {
        $this->methodManager = $methodManager;
    }

    public function processOutbound($path, &$options = array(), Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL)
    {
        if (array_key_exists('purl_context', $options) && $options['purl_context'] == false) {

            if (count($this->events) && $bubbleable_metadata) {
                $cacheContexts = $bubbleable_metadata->getCacheContexts();
                $cacheContexts[] = 'purl';
                $bubbleable_metadata->setCacheContexts($cacheContexts);
            }

            foreach ($this->events as $event) {
                $path = $this->exitContext($event, $path, $options);
            }

            return $path;
        }

        foreach ($this->events as $event) {
            $path = $this->enterContext($event, $path, $options);
        }

        return $path;
    }

    public function exitContext(ModifierMatchedEvent $event, $path, $options)
    {

        $method = $event->getMethod();
        $result = $method->exitContext($event->getModifier(), $path, $options);

        return $result === null ? $path : $result;

    }

    public function enterContext(ModifierMatchedEvent $event, $path, $options)
    {
        $method = $event->getMethod();
        $result = $method->enterContext($event->getModifier(), $path, $options);

        return $result === null ? $path : $result;

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
