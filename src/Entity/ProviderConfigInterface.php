<?php

namespace Drupal\purl\Entity;

interface ProviderConfigInterface
{
    public function getProviderKey();

    public function getLabel();

    public function getMethodKey();
}
