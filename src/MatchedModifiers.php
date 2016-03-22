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
     * @var ModifierMatchedEvent[]
     */
    public function getMatched()
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
}
