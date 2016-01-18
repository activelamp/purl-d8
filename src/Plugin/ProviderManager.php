<?php

namespace Drupal\purl\Plugin;

use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\purl\Plugin\Purl\Provider\ConfigurableInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Drupal\Core\Database\Connection;

class ProviderManager extends DefaultPluginManager implements ContainerAwareInterface
{

    use ContainerAwareTrait;

    /**
     * @var Drupal\purl\Plugin\Purl\Method\MethodInterface[]
     *
     * We store created instances here and return the right one when queried
     * for again. We only one one instance for each method plugin.
     */
    protected $providers = array();

    protected $connection;

    public function __construct(
        \Traversable $namespaces,
        CacheBackendInterface $cacheBackend,
        ModuleHandlerInterface $moduleHandler
    ) {
        parent::__construct(
            'Plugin/Purl/Provider',
            $namespaces,
            $moduleHandler,
            'Drupal\purl\Plugin\Purl\Provider\ProviderInterface',
            'Drupal\purl\Annotation\PurlProvider'
        );
        $this->setCacheBackend($cacheBackend, 'purl_provider_plugins');
    }

    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getProviderConfiguration($id)
    {
        $result = $this->connection->select('purl_providers_settings', 'p')
            ->fields('p', array('provider', 'method', 'settings'))
            ->condition('provider', $id)
            ->execute();

        $row = $result->fetch();

        return array(
            'method' => $row ? $row->method : null,
            'settings' => $row ? unserialize($row->settings) : array(),
        );
    }

    public function saveProviderConfiguration($providerId, $method, array $settings)
    {
        $this->deleteProviderConfiguration($providerId);
        $this->connection->insert('purl_providers_settings')
            ->fields(array(
                'provider' => $providerId,
                'method' => $method,
                'settings' => serialize($settings),
            ))->execute();
        $definitions = $this->findDefinitions();
        $definitions[$providerId] = array_merge($definitions[$providerId], array(
            'method' => $method,
            'settings' => $settings,
        ));
        $this->setCachedDefinitions($definitions);
    }

    public function deleteProviderConfiguration($providerId)
    {
        $this->connection->delete('purl_providers_settings')
            ->condition('provider', $providerId)
            ->execute();
        $definitions = $this->findDefinitions();
        $definitions[$providerId] = array_merge($definitions[$providerId], array(
            'method' => null,
            'settings' => array(),
        ));
        $this->setCachedDefinitions($definitions);
    }

    public function findDefinitions()
    {
        $definitions = parent::findDefinitions();

        foreach ($definitions as $id => $definition) {
            $config = $this->getProviderConfiguration($id);
            $definitions[$id]['method'] = $config['method'];
            $definitions[$id]['settings'] = $config['settings'];
        }

        return $definitions;
    }

    public function getProvider($id)
    {
        if (!isset($this->providers[$id])) {

            $plugin = $this->createInstance($id);
            $definition = $this->getDefinition($id);

            $plugin->setId($id);

            if ($plugin instanceof ContainerAwareInterface) {
                $plugin->setContainer($this->container);
            }

            if ($plugin instanceof ConfigurableInterface) {
                $plugin->setSettings($definition['settings']);
            }

            $this->providers[$id] = $plugin;
        }

        return $this->providers[$id];
    }

    public function hasProvider($id)
    {
        return isset($this->providers[$id]) || $this->hasDefinition($id);
    }
}
