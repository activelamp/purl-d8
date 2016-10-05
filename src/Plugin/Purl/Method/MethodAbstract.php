<?php

namespace Drupal\purl\Plugin\Purl\Method;

use Drupal\Component\Plugin\PluginBase;

abstract class MethodAbstract extends PluginBase implements MethodInterface
{
    public function getId() 
    {
        return $this->getPluginId();
    }

    public function getLabel()
    {
        return $this->pluginDefinition['label'];
    }
}
