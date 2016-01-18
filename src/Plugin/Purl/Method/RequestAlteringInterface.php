<?php

namespace Drupal\purl\Plugin\Purl\Method;

use Symfony\Component\HttpFoundation\Request;

/**
 *
 * This is used to signify that a method plugin would want to modify the
 * request, which requires re-initializing the request object.
 *
 * Method plugins that does not need to modify the request object do not need
 * to implement this, and the request will not be re-initialized for the
 * particular plugin.
 *
 */
interface RequestAlteringInterface
{
    public function alterRequest(Request $request, $identifier);
}
