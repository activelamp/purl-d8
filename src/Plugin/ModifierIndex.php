<?php

namespace Drupal\purl\Plugin;

use Drupal\purl\Plugin\Purl\Provider\ProviderInterface;
use Drupal\purl\Entity\Provider;
use Drupal\Core\Database\Connection;

/**
 * Create caching version by wrapping `getProviderModifiers`
 *
 */
class ModifierIndex
{
    protected $connection;

    protected $providerManager;

    public function __construct(ProviderManager $providerManager)
    {
        $this->providerManager = $providerManager;
    }

    public function findModifiers()
    {
        $ids = \Drupal::entityQuery('purl_provider')->execute();

        $modifiers = [];
        foreach (Provider::loadMultiple($ids) as $provider) {
            foreach ($this->getProviderModifiers($provider) as $modifier => $value) {
                $modifiers[] = [
                    'provider' => $provider,
                    'modifier' => $modifier,
                    'value' => $value
                ];
            }
        }

        return $modifiers;

    }

    public function getProviderModifiers(Provider $provider)
    {
        return $provider->getProviderPlugin()->getModifiers();
    }
}
