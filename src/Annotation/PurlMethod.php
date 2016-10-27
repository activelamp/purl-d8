<?php

namespace Drupal\purl\Annotation;

use Drupal\Component\Annotation\Plugin;
use Drupal\purl\Plugin\Purl\Method\MethodInterface;

/**
 * @Annotation
 */
class PurlMethod extends Plugin
{
    public function __construct($values)
    {
        parent::__construct($values);

        if (!isset($this->definition['label'])) {
            $id = preg_replace('/([^a-zA-Z0-9])+/', ' ', $this->definition['id']);
            $this->definition['label'] = ucwords($id);
        }

        if (!isset($this->definition['stages'])) {
          $this->definition['stage'] = [MethodInterface::STAGE_PROCESS_OUTBOUND];
        }
    }
}
