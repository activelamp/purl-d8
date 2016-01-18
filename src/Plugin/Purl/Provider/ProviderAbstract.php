<?php

namespace Drupal\purl\Plugin\Purl\Provider;

abstract class ProviderAbstract implements ProviderInterface, ConfigurableInterface
{
    protected $id;

    protected $settings;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSettings()
    {
        return $this->settings ?: array();
    }

    public function getDefaultSettings()
    {
        return array();
    }

    public function setSettings(array $settings)
    {
        return $this->settings = $settings;
    }
}
