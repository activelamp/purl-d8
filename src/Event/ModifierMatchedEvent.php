<?php

namespace Drupal\purl\Event;

use Symfony\Component\EventDispatcher\Event;


class ModifierMatchedEvent extends Event
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $providerKey;

    /**
     * @var string
     */
    protected $modifierKey;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param Request $request
     * @param string $providerKey
     * @param string $modifierKey
     * @param mixed $value
     */
    public function __construct(Request $rquest, $providerKey, $modifierKey, $value)
    {
        $this->request = $request;
        $this->providerKey = $providerKey;
        $this->modifierKey = $modifierKey;
        $this->value = $value;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return string
     */
    public function getProviderKey()
    {
        return $this->providerKey;
    }

    /**
     * @return string
     */
    public function getModifierKey()
    {
        return $this->modifierKey;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

}
