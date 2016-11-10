<?php

namespace Drupal\purl\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

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
   * @var string
   */
  protected $methodKey;

  /**
   * @param Request $request
   * @param string $providerKey
   * @param string $modifierKey
   * @param mixed $value
   */
  public function __construct(Request $request, $providerKey, $methodKey, $modifierKey, $value)
  {
    $this->request = $request;
    $this->providerKey = $providerKey;
    $this->modifierKey = $modifierKey;
    $this->methodKey = $methodKey;
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
  public function getProvider()
  {
    return $this->providerKey;
  }

  /**
   * @return string
   */
  public function getModifier()
  {
    return $this->modifierKey;
  }

  /**
   * @return string
   */
  public function getMethod()
  {
    return $this->methodKey;
  }

  /**
   * @return mixed
   */
  public function getValue()
  {
    return $this->value;
  }

}
