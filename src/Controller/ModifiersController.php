<?php

namespace Drupal\purl\Controller;

use Drupal\purl\Plugin\ProviderManager;
use Drupal\purl\Plugin\MethodPluginManager;
use Drupal\purl\Plugin\ModifierIndex;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\purl\Entity\Provider;

class ModifiersController extends BaseController
{
  protected $modifierIndex;

  protected $providerManager;

  protected $methodManager;

  public static function create(ContainerInterface $container)
  {
    return new self(
      $container->get('purl.modifier_index')
    );
  }

  public function __construct(
    ModifierIndex $modifierIndex
  ) {

    $this->modifierIndex = $modifierIndex;
  }

  private function stringify($value)
  {
    // This can be improved a lot more.
    if (is_scalar($value) || is_array($value)) {
      return json_encode($value);
    } else {
      return (string)$value;
    }
  }

  public function modifiers(Request $request)
  {
    $build = array();

    $ids = \Drupal::entityQuery('purl_provider')
      ->execute();

    $headers = array('provider', 'modifier', 'value');

    $headers = array_map(function ($header) {
      return array('data' => t($header));
    }, $headers);

    $rows = array();

    foreach ($this->modifierIndex->findModifiers() as $modifier) {

      $provider = $modifier['provider'];

      if (!$provider) {
        continue;
      }

      $row = array();

      $row[] = array(
        'data' => $provider->getLabel()
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
