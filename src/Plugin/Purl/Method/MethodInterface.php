<?php

namespace Drupal\purl\Plugin\Purl\Method;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing;

interface MethodInterface
{
    public function contains(Request $request, $modifier);

    public function enterContext($modifier, $path, array &$options);

    public function exitContext($modifier, $path, array &$options);

    public function getId();

    public function getLabel();
}
