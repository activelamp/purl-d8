<?php
/**
 * Created by PhpStorm.
 * User: bez
 * Date: 2016-02-04
 * Time: 5:04 PM
 */

namespace Drupal\purl;


use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Reference;

class PurlServiceProvider extends ServiceProviderBase
{
    public function alter(ContainerBuilder $container)
    {
        $urlGeneratorDefinition = $container->getDefinition('url_generator');
        $urlGeneratorDefinition->replaceArgument(0, new Reference('purl.url_generator'));
    }
}