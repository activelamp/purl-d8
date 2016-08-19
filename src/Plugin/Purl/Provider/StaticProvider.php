<?php

namespace Drupal\purl\Plugin\Purl\Provider;

use Drupal\purl\Annotation\PurlProvider;

/**
 * @PurlProvider(
 *      id="static",
 *      label=@Translation("Static")
 * )
 */
class StaticProvider extends ProviderAbstract
{
    public function getModifiers()
    {
        return array(
            'un' => 1,
            'deux' => 2,
            'trois' => 3,
            'quatre' => 4,
            'cuinq' => 5,
        );
    }
}
