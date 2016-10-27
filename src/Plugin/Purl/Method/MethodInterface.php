<?php

namespace Drupal\purl\Plugin\Purl\Method;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing;

interface MethodInterface
{
    const STAGE_PRE_GENERATE = 'purl.stage.pre_generate';

    const STAGE_PROCESS_OUTBOUND = 'purl.stage.process_outbound';

    public function contains(Request $request, $modifier);

    public function enterContext($modifier, $path, array &$options);

    public function exitContext($modifier, $path, array &$options);

    public function getId();

    public function getLabel();

    public function getStages();
}
