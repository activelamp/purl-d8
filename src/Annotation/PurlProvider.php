<?php

namespace Drupal\purl\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * @Annotation
 */
class PurlProvider extends Plugin
{
    public function __construct($values)
    {
        parent::__construct($values);

        if (!isset($this->definition['name'])) {
            $id = preg_replace('/([^a-zA-Z0-9])+/', ' ', $this->definition['id']);
            $this->definition['name'] = ucwords($id);
        }
    }
}
