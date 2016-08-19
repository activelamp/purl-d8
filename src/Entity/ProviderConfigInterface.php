<?php

namespace Drupal\purl\Entity;

interface ProviderConfigInterface
{
    public function getProvideKey();

    public function getLabel();

    public function getMethod();
}
