<?php
/**
 * Created by PhpStorm.
 * User: bez
 * Date: 10/26/16
 * Time: 2:03 PM
 */

namespace Drupal\purl\Plugin\Purl\Method;


interface PreGenerateHookInterface
{
    public function preGenerate(&$options, $modifier);
}