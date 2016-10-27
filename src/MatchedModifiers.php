<?php

namespace Drupal\purl;

use Drupal\purl\Event\ModifierMatchedEvent;

class MatchedModifiers
{
    /**
     * @var ModifierMatchedEvent[]
     */
    private $matched = array();

    /**
     * @return Event\ModifierMatchedEvent[]
     */
    public function getMatched()
    {
        return $this->getEvents();
    }

    /**
     * @return Event\ModifierMatchedEvent[]
     */
    public function getEvents()
    {
        return $this->matched;
    }

    /**
     * @param ModifierMatchedEvent $event
     *
     * @return null
     */
    public function add(ModifierMatchedEvent $event)
    {
        $this->matched[] = $event;
    }

    public function createContexts($action = null)
    {
      return array_map(function (ModifierMatchedEvent $event) use ($action) {
          return new Context($event->getModifier(), $event->getMethod(), $action);
      }, $this->getMatched());
    }
}
