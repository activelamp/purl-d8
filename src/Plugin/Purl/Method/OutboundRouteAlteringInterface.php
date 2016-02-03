<?php
/**
 * Created by PhpStorm.
 * User: bez
 * Date: 2016-02-02
 * Time: 1:05 PM
 */

namespace Drupal\purl\Plugin\Purl\Method;


use Drupal\Core\Render\BubbleableMetadata;
use Symfony\Component\Routing\Route;

interface OutboundRouteAlteringInterface
{
    public function alterOutboundRoute($routeName, $modifier, Route $route, array &$parameters, BubbleableMetadata $metadata = null);
}