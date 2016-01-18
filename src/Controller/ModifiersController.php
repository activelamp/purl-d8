<?php

namespace Drupal\purl\Controller;

use Drupal\purl\Plugin\ProviderManager;
use Drupal\purl\Plugin\MethodPluginManager;
use Drupal\purl\Plugin\ModifierIndex;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class ModifiersController extends BaseController
{
    protected $modifierIndex;

    protected $providerManager;

    protected $methodManager;

    public static function create(ContainerInterface $container)
    {
        return new self(
            $container->get('purl.plugin.provider_manager'),
            $container->get('purl.plugin.method_manager'),
            $container->get('purl.modifier_index')
        );
    }

    public function __construct(
        ProviderManager $providerManager,
        MethodPluginManager $methodManager,
        ModifierIndex $modifierIndex
    ) {

        $this->modifierIndex = $modifierIndex;
        $this->providerManager = $providerManager;
        $this->methodManager = $methodManager;
    }

    private function stringify($value)
    {
        // This can be improved a lot more.
        if (is_scalar($value) || is_array($value)) {
            return json_encode($value);
        } else {
            return (string) $value;
        }
    }

    public function modifiers(Request $request)
    {
        $build = array();

        $headers = array('provider', 'method', 'modifier', 'value');

        $headers = array_map(function ($header) {
            return array('data' => t($header));
        }, $headers);

        $rows = array();

        foreach ($this->modifierIndex->findModifiers() as $modifier) {

            $provider = $this->providerManager->getDefinition($modifier['provider']);
            $method = $this->methodManager->getDefinition($provider['method']);

            $row = array();

            $row[] = array(
                'data' => $provider['name'],
            );

            $row[] = array(
                'data' => $method['name'],
            );

            $row[] = array(
                'data' => array(
                    '#type' => 'html_tag',
                    '#tag' => 'code',
                    '#value' => $modifier['modifier'],
                ),
            );

            $row[] = array(
                'data' => array(
                    '#type' => 'html_tag',
                    '#tag' => 'code',
                    '#value' => $this->stringify($modifier['value']),
                ),
            );

            $rows[] = $row;
        }


        $build['modifiers'] = array(
            '#theme' => 'table',
            '#header' => $headers,
            '#rows' => $rows,
        );

        return $build;
    }

}
