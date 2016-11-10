<?php

namespace Drupal\purl\Event;

use Drupal\purl\Entity\Provider;
use Drupal\purl\MatchedModifiers;
use Drupal\purl\Plugin\MethodPluginManager;
use Drupal\purl\Plugin\ModifierIndex;
use Drupal\purl\Plugin\ProviderManager;
use Drupal\purl\Plugin\Purl\Method\RequestAlteringInterface;
use Drupal\purl\PurlEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestSubscriber implements EventSubscriberInterface
{
  /**
   * @var ModifierIndex
   */
  protected $modifierIndex;

  /**
   * @var MatchedModifiers
   */
  protected $matchedModifiers;

  public function __construct(
    ModifierIndex $modifierIndex,
    MatchedModifiers $matchedModifiers
  )
  {
    $this->modifierIndex = $modifierIndex;
    $this->matchedModifiers = $matchedModifiers;
  }

  public static function getSubscribedEvents()
  {
    return array(
      // RouterListener comes in at 32. We need to go before it.
      KernelEvents::REQUEST => array('onRequest', 50),
    );
  }

  /**
   * @return \Drupal\purl\Modifier[]
   */
  protected function getModifiers()
  {
    return $this->modifierIndex->findAll();
  }

  protected function getMethodForProvider($providerId)
  {
    return Provider::load($providerId)->getMethodPlugin();
  }

  public function onRequest(GetResponseEvent $event, $eventName, EventDispatcherInterface $dispatcher)
  {
    $request = $event->getRequest();
    $modifiers = $this->getModifiers();

    $matches = array();

    foreach ($modifiers as $modifier) {

      $provider = $modifier->getProvider();
      $modifierKey = $modifier->getModifierKey();
      $method = $modifier->getMethod();

      if ($method->contains($request, $modifierKey)) {
        $matches[$provider->getProviderId()] = array(
          'method' => $method,
          'modifier' => $modifierKey,
          'provider_key' => $provider->getProviderId(),
          'provider' => $modifier->getProvider(),
          'value' => $modifier->getValue()
        );
      }
    }

    foreach ($matches as $match) {

      if (!$match['method'] instanceof RequestAlteringInterface) {
        continue;
      }

      $match['method']->alterRequest($request, $match['modifier']);
      $this->reinitializeRequest($request);
    }

    foreach ($matches as $match) {
      $event = new ModifierMatchedEvent(
        $request,
        $match['provider_key'],
        $match['method'],
        $match['modifier'],
        $match['value']
      );
      $dispatcher->dispatch(PurlEvents::MODIFIER_MATCHED, $event);
      $this->matchedModifiers->add($event);
    }

    $request->attributes->set('purl.matched_modifiers', $matches);
  }

  /**
   * Since the Request object is absent of APIs for modifying parts of the
   * request, we will need to run its iniitalize method to make it do it
   * itself. This will be done after a method plugin alters the server
   * attributes i.e. $request->server->set('REQUEST_URI', '/new/uri')
   *
   * I don't have a better solution that doesn't feel hacky.
   */
  private function reinitializeRequest(Request $request)
  {
    $request->initialize(
      $request->query->all(),
      $request->request->all(),
      $request->attributes->all(),
      $request->cookies->all(),
      $request->files->all(),
      $request->server->all(),
      $request->getContent()
    );
  }
}
