<?php

namespace Drupal\purl\Plugin;

interface MethodPluginManagerInterface
{
    /**
     * @param string $id
     * @return Drupal\purl\Plugin\Purl\Context\MethodPluginInterface
     */
    public function getMethodPlugin($id);

    /**
     * @param string $id
     * @return boolen
     */
    public function hasMethodPlugin($id);
}
