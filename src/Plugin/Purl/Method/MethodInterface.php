<?php

namespace Drupal\purl\Plugin\Purl\Method;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing;

interface MethodInterface
{
    public function contains(Request $request, $identifier);
}
