<?php

namespace Drupal\purl\Plugin\Purl\Context;

interface ContextProviderInterface
{
    public function createContext(Request $request, $identifier, $value);
}
