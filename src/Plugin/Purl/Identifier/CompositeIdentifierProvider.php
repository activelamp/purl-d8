<?php

namespace Drupal\purl\Plugin\Purl\Identifier;

class CompositeIdentifierProvider implements IdentifierProviderInterface
{
    /**
     * @var array|IdentifierProviderInterface[]
     */
    protected $providers;

    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    public function getIdentifiers()
    {
        $identifiers = array();

        foreach ($this->providers as $provider) {
            $identifiers = array_merge($identifiers, $provider->getIdentifiers());
        }

        return $identifiers;
    }
}
