<?php

namespace Drupal\purl;


use Drupal\purl\Plugin\Purl\Method\MethodInterface;

class Context
{
    private $modifier;
    /**
     * @var MethodInterface
     */
    private $method;

    private $action;

    const ENTER_CONTEXT = 'purl.enter_context';

    const EXIT_CONTEXT = 'purl.exit_context';



    public function __construct($modifier, MethodInterface $method, $action = null)
    {
      $this->modifier = $modifier;
      $this->method = $method;
      $this->action = $action ?: self::ENTER_CONTEXT;
    }

    /**
     * @return mixed
     */
    public function getModifier()
    {
      return $this->modifier;
    }

    /**
     * @return MethodInterface
     */
    public function getMethod()
    {
      return $this->method;
    }

    /**
     * @return string
     */
    public function getAction()
    {
      return $this->action;
    }
}