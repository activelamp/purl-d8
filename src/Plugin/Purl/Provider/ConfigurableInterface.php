<?php

namespace Drupal\purl\Plugin\Purl\Provider;

interface ConfigurableInterface
{
    public function getSettings();

    public function getDefaultSettings();

    public function setSettings(array $settings);
}
