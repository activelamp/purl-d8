<?php


namespace Drupal\purl\Utility;


use Drupal\Core\GeneratedUrl;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Utility\UnroutedUrlAssembler;
use Drupal\purl\Context;
use Drupal\purl\ContextHelper;
use Drupal\purl\MatchedModifiers;
use Symfony\Component\HttpFoundation\RequestStack;

class PurlAwareUnroutedUrlAssembler extends UnroutedUrlAssembler
{
  /**
   * @var ContextHelper
   */
  private $contextHelper;
  /**
   * @var MatchedModifiers
   */
  private $matchedModifiers;

  public function __construct(
    RequestStack $request_stack,
    OutboundPathProcessorInterface $path_processor,
    $filter_protocols,
    ContextHelper $contextHelper,
    MatchedModifiers $matchedModifiers
  ) {
    parent::__construct($request_stack, $path_processor, $filter_protocols);
    $this->contextHelper = $contextHelper;
    $this->matchedModifiers = $matchedModifiers;
  }

  public function buildLocalUrl($uri, array $options = [], $collect_bubbleable_metadata = FALSE)
  {
    if (!array_key_exists('purl_context', $options)) {
      return parent::buildLocalUrl($uri, $options, $collect_bubbleable_metadata);
    } else {
      return $this->buildLocalUrlWithPurlContexts($uri, $options, $collect_bubbleable_metadata);
    }
  }

  /**
   * @param $url
   * @param array $options
   * @param bool $collect_bubbleable_metadata
   * @return string
   */
  private function buildLocalUrlWithPurlContexts($uri, array $options, $collect_bubbleable_metadata = FALSE)
  {
    $generated_url = $collect_bubbleable_metadata ? new GeneratedUrl() : NULL;

    $this->addOptionDefaults($options);
    $request = $this->requestStack->getCurrentRequest();

    // Remove the base: scheme.
    // @todo Consider using a class constant for this in
    //   https://www.drupal.org/node/2417459
    $uri = substr($uri, 5);

    $uri = ltrim($uri, '/');

    // Add any subdirectory where Drupal is installed.
    $current_base_path = $request->getBasePath() . '/';

    if (array_key_exists('purl_context', $options) && $options['purl_context'] === false) {
      $contexts = $this->matchedModifiers->createContexts(Context::EXIT_CONTEXT);
    } else {
      $contexts = $this->contextHelper->createContextsFromMap($options['purl_context']);
    }

    $uri = $this->contextHelper->processOutbound($contexts, $uri, $options, $request, null);

    if ($options['absolute']) {

      $host = $request->getScheme() . '://' . $options['host'];
      if ($request->getPort()) {
        $host . ':' . $request->getPort();
      }

      $current_base_url = $host . $current_base_path;

      if (isset($options['https'])) {
        if (!empty($options['https'])) {
          $base = str_replace('http://', 'https://', $current_base_url);
          $options['absolute'] = TRUE;
        }
        else {
          $base = str_replace('https://', 'http://', $current_base_url);
          $options['absolute'] = TRUE;
        }
      }
      else {
        $base = $current_base_url;
      }
      if ($collect_bubbleable_metadata) {
        $generated_url->addCacheContexts(['url.site']);
      }
    }
    else {
      $base = $current_base_path;
    }

    $prefix = empty($uri) ? rtrim($options['prefix'], '/') : $options['prefix'];

    $uri = str_replace('%2F', '/', rawurlencode($prefix . $uri));
    $query = $options['query'] ? ('?' . UrlHelper::buildQuery($options['query'])) : '';
    $url = $base . $options['script'] . $uri . $query . $options['fragment'];
    return $collect_bubbleable_metadata ? $generated_url->setGeneratedUrl($url) : $url;
  }
}