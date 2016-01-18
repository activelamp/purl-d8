<?php

namespace Drupal\purl\Plugin\Purl\Identifier;

class TestProvider implements IdentifierProviderInterface
{
    public function __construct()
    {
    }

    public function getIdentifiers()
    {
        return array(
            array(
                "id" => "my_context_1",
                "provider" => "event_provider",
                "method" => "path",
                "modifier" => "events",
                "data" => 1
            ),
            array(
                "id" => "my_context_2",
                "provider" => "event_provider",
                "method" => "path",
                "modifier" => "dev-events",
                "data" => 2
            ),
            array(
                "id" => "my_context_3",
                "provider" => "event_provider",
                "method" => "subdomain",
                "modifier" => "qa-events",
                "data" => 3
            ),
        );
    }
}
