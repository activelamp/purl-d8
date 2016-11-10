<?php

namespace Drupal\purl\Plugin;

interface ContextProviderManagerInterface
{
    /**
     * @param string $id
     * @return Drupal\purl\Plugin\Purl\Context\ContextProviderInterface
     */
    public function getContextProvider($id);

    /**
     * @param string $id
     * @return Drupal\purl\Plugin\Purl\Context\ContextProviderInterface
     */
    public function hasContextProvider($id);
}
