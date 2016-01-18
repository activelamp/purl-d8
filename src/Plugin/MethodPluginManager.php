<?php

namespace Drupal\purl\Plugin;

use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class MethodPluginManager extends DefaultPluginManager implements MethodPluginManagerInterface, ContainerAwareInterface
{

    use ContainerAwareTrait;

    /**
     * @var Drupal\purl\Plugin\Purl\Method\MethodInterface[]
     *
     * We store created instances here and return the right one when queried
     * for again. We only one one instance for each method plugin.
     */
    protected $methodPlugins = array();

    public function __construct(
        \Traversable $namespaces,
        CacheBackendInterface $cacheBackend,
        ModuleHandlerInterface $moduleHandler
    ) {
        parent::__construct(
            'Plugin/Purl/Method',
            $namespaces,
            $moduleHandler,
            'Drupal\purl\Plugin\Purl\Method\MethodInterface',
            'Drupal\purl\Annotation\PurlMethod'
        );

        $this->setCacheBackend($cacheBackend, 'purl_method_plugins');
    }

    public function getMethodPlugin($id)
    {
        if (!isset($this->methodPlugins[$id])) {
            $plugin = $this->createInstance($id);

            if ($plugin instanceof ContainerAwareInterface) {
                $plugin->setContainer($this->container);
            }

            $this->methodPlugins[$id] = $plugin;
        }

        return $this->methodPlugins[$id];
    }

    public function hasMethodPlugin($id)
    {
        return isset($this->methodPlugins[$id]) || $this->hasDefinition($id);
    }
}
