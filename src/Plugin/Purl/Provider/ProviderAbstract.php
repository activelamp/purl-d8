<?php

namespace Drupal\purl\Plugin\Purl\Provider;

use Drupal\Component\Plugin\PluginBase;
use Drupal\purl\Modifier;

abstract class ProviderAbstract extends PluginBase implements ProviderInterface
{
  public function getProviderId()
  {
    return $this->getPluginId();
  }

  public function getLabel()
  {
    return $this->pluginDefinition['label'];
  }
}
