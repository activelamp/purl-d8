<?php

namespace Drupal\purl\Controller;

use Drupal\purl\Plugin\ProviderManager;
use Drupal\purl\Plugin\MethodPluginManager;
use Drupal\purl\Plugin\ModifierIndex;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class ProvidersController extends BaseController
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

    public function saveProviderSettings(Request $request)
    {
        if ($request->getMethod() === "POST") {
            $providers = $request->request->get('providers', array());
            foreach ($providers as $providerId => $data) {

                $provider = $this->providerManager->getProvider($providerId);

                if ($data['method']) {
                    $this->providerManager->saveProviderConfiguration(
                        $providerId,
                        $data['method'],
                        isset($data['settings']) ? $data['settings'] : array()
                    );
                    $this->modifierIndex->indexModifiers($provider, $data['method']);
                } else {
                    $this->providerManager->deleteProviderConfiguration($providerId);
                    $this->modifierIndex->deleteEntriesByProvider($providerId);
                }
            }
        }

        return $this->redirect('purl.admin');
    }

    public function providers(Request $request)
    {
        $methods = array('' => sprintf('-- %s --', t('Disabled')));

        foreach ($this->methodManager->getDefinitions() as $definition) {
            $methods[$definition['id']] = $definition['name'];
        }

        $providers = array();

        $defaultConfig = array(
            'method' => null,
            'settings' => array(),
        );

        $headers = array('providers', 'methods', 'settings');
        $rows = array();

        foreach ($this->providerManager->getDefinitions() as $id => $definition) {;
            $row = array(
                array(
                    'data' => $definition['name']
                ),
                array(
                    'data' => array(
                        '#theme' => 'select',
                        '#value' => $definition['method'],
                        '#options' => $methods,
                        '#name' => sprintf('providers[%s][method]', $id),
                    ),
                ),
                array(
                    'data' => '',
                ),
            );
            $rows[] = $row;
        }

        $tableData = array(
            '#theme' => 'table',
            '#header' => array_map(function ($header) {
                return array('data' => t($header));
            }, $headers),
            '#rows' => $rows,
        );

        $build = array();

        $build['providers_settings_form'] = array(
            '#type' => 'html_tag',
            '#tag' => 'form',
            '#attributes' => array(
                'method' => 'POST',
                'action' => \Drupal::url('purl.admin.save_providers_config'),
            ),
        );

        $submitData = array(
            '#type' => 'html_tag',
            '#tag' => 'input',
            '#attributes' => array(
                'class' => array('button button--primary form-submit'),
                'type' => 'submit',
                'value' => 'Save',
            ),
        );

        $formContents = array(
            'table' => $tableData,
            'submit' => $submitData,
        );
        $form = drupal_render($formContents);

        $build['providers_settings_form']['#value'] = $form;

        return $build;
    }
}
