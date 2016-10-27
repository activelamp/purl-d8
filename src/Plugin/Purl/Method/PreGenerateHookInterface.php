<?php

namespace Drupal\purl\Plugin\Purl\Method;

interface PreGenerateHookInterface
{
    /**
     * @param $modifier
     * @param $name
     * @param $parameters
     * @param $options
     * @param bool $collect_bubblable_metadata
     * @return mixed
     */
    public function preGenerateEnter($modifier, $name, &$parameters, &$options, $collect_bubblable_metadata = false);

    /**
     * @param $modifier
     * @param $name
     * @param $parameters
     * @param $options
     * @param bool $collect_bubblable_metadata
     * @return mixed
     */
    public function preGenerateExit($modifier, $name, &$parameters, &$options, $collect_bubblable_metadata = false);
}