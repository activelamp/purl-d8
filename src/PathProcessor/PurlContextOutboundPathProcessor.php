<?php

namespace Drupal\purl\PathProcessor;

use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\purl\Context;
use Drupal\purl\ContextHelper;
use Drupal\purl\MatchedModifiers;
use Symfony\Component\HttpFoundation\Request;

class PurlContextOutboundPathProcessor implements OutboundPathProcessorInterface
{
    /**
     * @var MatchedModifiers
     */
    private $matchedModifiers;
    /**
     * @var ContextHelper
     */
    private $contextHelper;

    public function __construct(MatchedModifiers $matchedModifiers, ContextHelper $contextHelper)
    {
      $this->matchedModifiers = $matchedModifiers;
      $this->contextHelper = $contextHelper;
    }

    public function processOutbound($path, &$options = array(), Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL)
    {
        if (array_key_exists('purl_context', $options) && $options['purl_context'] == false) {

            if (count($this->matchedModifiers->getMatched()) && $bubbleable_metadata) {
                $cacheContexts = $bubbleable_metadata->getCacheContexts();
                $cacheContexts[] = 'purl';
                $bubbleable_metadata->setCacheContexts($cacheContexts);
            }

            return $this->contextHelper->processOutbound(
              $this->matchedModifiers->createContexts(Context::EXIT_CONTEXT),
              $path,
              $options,
              $request,
              $bubbleable_metadata
            );
        }

        return $this->contextHelper->processOutbound(
          $this->matchedModifiers->createContexts(),
          $path,
          $options,
          $request,
          $bubbleable_metadata
        );
    }
}
