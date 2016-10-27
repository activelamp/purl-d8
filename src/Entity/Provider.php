<?php

namespace Drupal\purl\Entity;

use Drupal;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\purl\Modifier;
use Drupal\purl\Plugin\Purl\Method\MethodInterface;
use Drupal\purl\Plugin\Purl\Provider\ProviderInterface as ProviderPluginInterface;
use Drupal\purl\Plugin\Purl\Method\MethodInterface as MethodPluginInterface;
use Drupal\purl\Plugin\Purl\Provider\ProviderInterface;

/**
 * @ConfigEntityType(
 *  id = "purl_provider",
 *  label = @Translation("PURL Provider"),
 *  handlers = {
 *    "list_builder" = "Drupal\purl\Controller\ProviderListBuilder",
 *    "form" = {
 *      "add" = "Drupal\purl\Form\ProviderForm",
 *      "edit" = "Drupal\purl\Form\ProviderForm",
 *      "delete" = "Drupal\purl\Form\DeleteProviderForm"
 *    }
 *  },
 *  config_prefix = "purl_provider",
 *  admin_permission = "administer site configuration",
 *  entity_keys = {
 *    "id" = "provider_key",
 *    "label" = "label"
 *  },
 *  links = {
 *    "edit-form" = "/admin/config/search/purl/provider/{purl}",
 *    "delete-form" = "/admin/config/search/purl/provider/{purl}/delete"
 *  }
 * )
 */
class Provider extends ConfigEntityBase implements ProviderConfigInterface, ProviderInterface
{

  protected static $providerManager;

  protected static $methodManager;

  protected $methodPlugin;

  protected $providerPlugin;

  /**
   * @return int|null|string
   */
  public function getProviderKey()
  {
    return $this->id();
  }

  /**
   * @return mixed|null|string
   */
  public function getLabel()
  {
    return $this->label();
  }

  /**
   * @return mixed|null
   */
  public function getMethodKey()
  {
    return $this->get('method_key');
  }

  /**
   * @return null
   */
  public function id()
  {
    return $this->get('provider_key') ?: null;
  }

  /**
   * @param MethodPluginInterface $method
   */
  private function setMethodPlugin(MethodPluginInterface $method)
  {
    $this->methodPlugin = $method;
  }

  /**
   * @param ProviderPluginInterface $provider
   */
  private function setProviderPlugin(ProviderPluginInterface $provider)
  {
    $this->providerPlugin = $provider;
  }

  /**
   * @return ProviderInterface
   */
  public function getProviderPlugin()
  {
    return $this->providerPlugin;
  }

  /**
   * @return MethodInterface
   */
  public function getMethodPlugin()
  {
    return $this->methodPlugin;
  }

  /**
   * {@inheritdoc}
   */
  public static function postLoad(EntityStorageInterface $storage, array &$entities)
  {
    foreach ($entities as $entity) {
      $entity->setMethodPlugin(self::getMethodManager()->getMethodPlugin($entity->getMethodKey()));
      $entity->setProviderPlugin(self::getProviderManager()->getProvider($entity->id()));
    }
  }

  /**
   * @return Drupal\purl\Plugin\ProviderManager
   */
  protected static function getProviderManager()
  {
    if (static::$providerManager === null) {
      static::$providerManager = Drupal::service('purl.plugin.provider_manager');
    }
    return static::$providerManager;
  }

  /**
   * @return Drupal\purl\Plugin\MethodPluginManager
   */
  protected static function getMethodManager()
  {
    if (static::$methodManager === null) {
      static::$methodManager = Drupal::service('purl.plugin.method_manager');
    }
    return static::$methodManager;
  }

  /**
   * @return array
   */
  public function getModifierData()
  {
    return $this->getProviderPlugin()->getModifierData();
  }

  public function getProviderId()
  {
    return $this->getProviderPlugin()->getProviderId();
  }
}


