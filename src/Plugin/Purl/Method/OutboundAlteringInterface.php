<?php

namespace Drupal\purl\Plugin\Purl\Method;

use Symfony\Component\HttpFoundation\Request;

interface OutboundAlteringInterface
{
    public function alterOutbound($path, $modifier, &$options = null, Request $request = null);
}
