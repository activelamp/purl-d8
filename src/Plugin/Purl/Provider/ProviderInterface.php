<?php

namespace Drupal\purl\Plugin\Purl\Provider;

use Drupal\purl\Plugin\Purl\Method\MethodInterface;

interface ProviderInterface
{
    /**
     * @return array
     *
     * Should return an array of modifier arrays, i.e.
     *
     * return array(
     *    array(
     *      "modifier" => "modifier-1",
     *      "value" => 1,
     *    ),
     *    array(
     *      "modifier" => "modifier-2",
     *      "value" => 2,
     *    )
     * )
     *
     * In theory, "value" can be anything. However, it must be something that
     * can be serialized for caching and can be cheaply unserialized during
     * run-time.
     */
    public function getModifiers();

    public function setId($id);

    public function getId();
}
