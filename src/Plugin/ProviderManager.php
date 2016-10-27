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
   * @var Drupal\purl\Plugin\Purl\Provider\ProviderInterface[]
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
  )
  {
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

  public function getProvider($id)
  {
    if (!isset($this->providers[$id])) {

      $plugin = $this->createInstance($id);

      if ($plugin instanceof ContainerAwareInterface) {
        $plugin->setContainer($this->container);
      }

      $this->providers[$id] = $plugin;
    }

    return $this->providers[$id];
  }
}
