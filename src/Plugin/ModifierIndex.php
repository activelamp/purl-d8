<?php

namespace Drupal\purl\Plugin;

use Drupal\purl\Plugin\Purl\Provider\ProviderInterface;
use Drupal\Core\Database\Connection;

class ModifierIndex
{
    protected $connection;

    protected $providerManager;

    public function __construct(ProviderManager $providerManager, Connection $connection)
    {
        $this->providerManager = $providerManager;
        $this->connection = $connection;
    }

    public function findModifiers()
    {
        $result = $this->connection->select('purl_modifiers', 'p')
            ->fields('p', array('provider', 'modifier', 'value'))
            ->execute();

        return array_map(function ($row) {
            return array_merge((array) $row, array('value' => unserialize($row->value)));
        }, $result->fetchAll());
    }

    public function indexModifiers(ProviderInterface $provider, $method)
    {
        $id = $provider->getId();
        $modifiers = $provider->getModifiers();

        if (!is_array($modifiers)) {
            return;
        }

        $this->deleteEntriesByProvider($provider->getId());

        foreach ($modifiers as $modifier => $value) {
            $this->connection->insert('purl_modifiers')
                ->fields(array(
                    'modifier' => $modifier,
                    'provider' => $id,
                    'value' => serialize($value)
                ))->execute();
        }

        $this->connection->update('purl_providers_settings')
            ->condition('provider', $provider->getId())
            ->fields(array('rebuild' => 0))
            ->execute();
    }

    public function deleteEntriesByProvider($providerId)
    {
        $this->connection->delete('purl_modifiers')
            ->condition('provider', $providerId)
            ->execute();
    }

    public function deleteAll()
    {
        $this->connection->delete('purl_modifiers')->execute();
    }

    public function rebuild($providerId = null, $immediately = false)
    {

        if ($immediately) {

            if (!$providerId) {
                throw new \BadMethodCallException('A provider must be specified during immediate rebuilds.');
            }

            $provider = $this->providerManager->getProvider($providerId);
            $definition = $this->providerManager->getDefinition($providerId);
            if (!$definition['method']) {
                return;
            }
            $this->indexModifiers($providerId, $definition['method']);
        } else {
            $statement = $this->connection->update('purl_providers_settings')->fields(array('rebuild' => 1));

            if ($providerId) {
                $statement->condition('provider', $providerId);
            }
            $statement->execute();
        }
    }

    public function performDueRebuilds()
    {
        $result = $this->connection->select('purl_providers_settings', 'p')
            ->fields('p', array('provider', 'method'))
            ->condition('p.rebuild', 1)
            ->execute();

        foreach ($result->fetchAll() as $row) {
            $provider = $this->providerManager->getProvider($row->provider);
            $this->indexModifiers($provider, $row->method);
        }
    }
}
