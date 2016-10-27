<?php

namespace Drupal\purl\Plugin\Purl\Provider;

use Drupal\purl\Modifier;

interface ProviderInterface
{
    /**
     * @return array
     */
    public function getModifierData();

    public function getProviderId();

    public function getLabel();
}
