<?php

namespace Drupal\purl\Cache\Context;


use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;
use Drupal\purl\Event\ModifierMatchedEvent;
use Drupal\purl\PurlEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PurlCacheContext implements CacheContextInterface, EventSubscriberInterface
{

    protected $contexts = array();

    /**
     * Returns the label of the cache context.
     *
     * @return string
     *   The label of the cache context.
     */
    public static function getLabel()
    {
        return t('PURL Context');
    }

    /**
     * Returns the string representation of the cache context.
     *
     * A cache context service's name is used as a token (placeholder) cache key,
     * and is then replaced with the string returned by this method.
     *
     * @return string
     *   The string representation of the cache context.
     */
    public function getContext()
    {
        return json_encode($this->contexts);
    }

    /**
     * Gets the cacheability metadata for the context.
     *
     * There are three valid cases for the returned CacheableMetadata object:
     * - An empty object means this can be optimized away safely.
     * - A max-age of 0 means that this context can never be optimized away. It
     *   will never bubble up and cache tags will not be used.
     * - Any non-zero max-age and cache tags will bubble up into the cache item
     *   if this is optimized away to allow for invalidation if the context
     *   value changes.
     *
     *
     * @return \Drupal\Core\Cache\CacheableMetadata
     *   A cacheable metadata object.
     */
    public function getCacheableMetadata()
    {
        return new CacheableMetadata();
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            PurlEvents::MODIFIER_MATCHED => array('onMatch'),
        ];
    }

    public function onMatch(ModifierMatchedEvent $event)
    {
        $this->contexts[$event->getMethod()]  = $event->getModifier();
        ksort($this->contexts);
    }
}