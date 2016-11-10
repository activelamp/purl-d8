<?php

namespace Drupal\purl;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\purl\Entity\Provider;
use Drupal\purl\Plugin\Purl\Method\MethodInterface;
use Symfony\Component\HttpFoundation\Request;

class ContextHelper
{

  /**
   * @var EntityStorageInterface
   */
  protected $storage;

  public function __construct(EntityStorageInterface $storage)
  {
    $this->storage = $storage;
  }

  /**
   * @param array $contexts
   * @param $path
   * @param array $options
   * @param Request|null $request
   * @param BubbleableMetadata|null $metadata
   * @return mixed
   */
  public function processOutbound(array $contexts, $path, array &$options, Request $request = null, BubbleableMetadata $metadata = null)
  {

    $result = $path;

    /** @var Context $context */
    foreach ($contexts as $context) {

      if (!in_array(MethodInterface::STAGE_PROCESS_OUTBOUND, $context->getMethod()->getStages())) {
        continue;
      }

      $contextResult = null;

      if ($context->getAction() == Context::ENTER_CONTEXT) {
        $contextResult = $context->getMethod()->enterContext($context->getModifier(), $result, $options);
      } elseif ($context->getAction() == Context::EXIT_CONTEXT) {
        $contextResult = $context->getMethod()->exitContext($context->getModifier(), $result, $options);
      }

      $result = $contextResult ?: $result;
    }

    return $result;
  }

  /**
   * @param array $contexts
   * @param $routeName
   * @param array $parameters
   * @param array $options
   * @param $collect_bubblable_metadata
   */
  public function preGenerate(array $contexts, $routeName, array &$parameters, array &$options, $collect_bubblable_metadata)
  {
    $this->ensureContexts($contexts);

    /** @var Context $context */
    foreach ($contexts as $context) {

      if (!in_array(MethodInterface::STAGE_PRE_GENERATE, $context->getMethod()->getStages())) {
        continue;
      }

      if ($context->getAction() == Context::ENTER_CONTEXT) {
        $context->getMethod()->preGenerateEnter($context->getModifier(), $routeName, $parameters, $options, $collect_bubblable_metadata);
      } elseif ($context->getAction() == Context::EXIT_CONTEXT) {
        $context->getMethod()->preGenerateExit($context->getModifier(), $routeName, $parameters, $options, $collect_bubblable_metadata);
      }

    }
  }

  /**
   * @param array $contexts
   * @return bool
   */
  private function ensureContexts(array $contexts)
  {
    foreach ($contexts as $index => $context) {
      if (!$context instanceof Context) {
        throw new \InvalidArgumentException(sprintf('#%d is not a context.', $index + 1));
      }
    }
  }

  /**
   * @param array $map
   * @return array
   */
  public function createContextsFromMap(array $map)
  {
    if (count($map) === 0) {
      return [];
    }

    $providers = $this->storage->loadMultiple(array_keys($map));

    return array_map(function (Provider $provider) use ($map) {
      return new Context($map[$provider->id()], $provider->getMethodPlugin());
    }, $providers);
  }
}