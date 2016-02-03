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
    protected $events = array();

    protected $methodManager;

    public function __construct(MethodPluginManager $methodManager)
    {
        $this->methodManager = $methodManager;
    }

    public function processOutbound($path, &$options = array(), Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL)
    {
        if (array_key_exists('purl_context', $options) && $options['purl_context'] == false) {
            return $path;
        }

        foreach ($this->events as $event) {
            $method = $this->methodManager->getMethodPlugin($event->getMethod());
            if ($method instanceof OutboundAlteringInterface) {
                $path = $method->alterOutbound($path, $event->getModifier(), $options, $request);
            }
        }

        return $path;
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
