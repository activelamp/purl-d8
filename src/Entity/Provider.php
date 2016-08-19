<?php

namespace Drupal\purl\Entity;

use Drupal;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\purl\Plugin\Purl\Provider\ProviderInterface as ProviderPluginInterface;
use Drupal\purl\Plugin\Purl\Method\MethodInterface as MethodPluginInterface;

/**
 * @ConfigEntityType(
 *  id = "purl_provider",
 *  label = @Translation("PURL Provider"),
 *  handlers = {
 *      "list_builder" = "Drupal\purl\Controller\ProviderListBuilder",
 *      "form" = {
 *          "add" = "Drupal\purl\Form\ProviderForm",
 *          "edit" = "Drupal\purl\Form\ProviderForm",
 *          "delete" = "Drupal\purl\Form\DeleteProviderForm"
 *      }
 *  },
 *  config_prefix = "purl_provider",
 *  admin_permission = "administer site configuration",
 *  entity_keys = {
 *      "id" = "provider_key",
 *      "label" = "label"
 *  },
 *  links = {
 *      "edit-form" = "/admin/config/search/purl/provider/{purl}",
 *      "delete-form" = "/admin/config/search/purl/provider/{purl}/delete"
 *  }
 * )
 */
class Provider extends ConfigEntityBase implements ProviderConfigInterface
{

    protected static $providerManager;

    protected static $methodManager;

    protected $methodPlugin;

    protected $providerPlugin;

    public function getProviderKey()
    {
        return $this->id();
    }

    public function getLabel()
    {
        return $this->label();
    }

    public function getMethodKey()
    {
        return $this->get('method_key');
    }

    public function id()
    {
        return $this->get('provider_key') ?: null;
    }

    private function setMethodPlugin(MethodPluginInterface $method)
    {
        $this->methodPlugin = $method; 
    }

    private function setProviderPlugin(ProviderPluginInterface $provider)
    {
        $this->providerPlugin = $provider; 
    }

    public function getProviderPlugin()
    {
       return $this->providerPlugin; 
    }

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

    protected static function getProviderManager()
    {
        if (static::$providerManager === null) {
            static::$providerManager = Drupal::service('purl.plugin.provider_manager');
        }
        return static::$providerManager;
    }

    protected static function getMethodManager()
    {
        if (static::$methodManager === null) {
            static::$methodManager = Drupal::service('purl.plugin.method_manager');
        }
        return static::$methodManager;
    }
}


