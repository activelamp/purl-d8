<?php

namespace Drupal\purl\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * @ConfigEntityType(
 *  id = "purl_provider",
 *  label = @Translation("PURL Provider"),
 *  handlers = {
 *      "list_builder" = "Drupal\purl\Controller\PurlProviderListBuilder",
 *      "form" = {
 *          "add" = "Drupal\purl\Form\ProviderForm",
 *          "edit" = "Drupal\purl\Form\ProviderForm",
 *          "delete" = "Drupal\purl\Form\DeleteProviderForm"
 *      }
 *  },
 *  config_prefix = "purl_provider",
 *  admin_permission = "administer site configuration",
 *  entity_keys = {
 *      "provider_key" = "provider_key",
 *      "label" = "label"
 *  },
 *  links = {
 *      "edit-form" = "/admin/config/system/purl/{purl}",
 *      "delete-form" = "/admin/config/system/purl/{purl}/delete"
 *  }
 * )
 */
class Provider extends ConfigEntityBase implements ProviderConfigInterface
{
    protected $providerKey;

    protected $label;

    public function getProviderKey()
    {
        return $this->providerKey;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getMethod()
    {
        return $this->method;
    }
}


