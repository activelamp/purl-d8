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

/**
 * ALTERNATIVE APPROACH IS ENCAPSULATE METHOD PLUGIN LOGIC WITH A PATH
 * PROCESSOR, AND DO MOST LOGIC WITHIN THE CONFINES OF Symfony\(Cmf\)?Routing
 */
class RequestSubscriber implements EventSubscriberInterface
{

    /**
     * @var ModifierIndex
     */
    protected $modifierIndex;

    public function __construct(
        ModifierIndex $modifierIndex,
        MatchedModifiers $matchedModifiers
    ) {
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

    protected function getModifiers()
    {
        return $this->modifierIndex->findModifiers();
    }

    public function getProvider($id)
    {
        return Provider::load($id);
    }

    public function onRequest(GetResponseEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $request = $event->getRequest();
        $modifiers = $this->getModifiers();

        $matchedModifiers = array();
        $requestAlteringMethods = array();

        foreach ($modifiers as $modifier) {

            $provider = $modifier['provider'];
            $modifier['provider_key'] = $provider->id();

            if (!$provider) {
                continue;
            }

            $modifierKey = $modifier['modifier'];

            if (!$provider->getMethodPlugin()) {
                continue;
            }

            $methodPlugin = $provider->getMethodPlugin();
            $contains = $methodPlugin->contains($request, $modifierKey);

            if ($contains) {
                $matchedModifiers[] = array(
                    'method' => $methodPlugin,
                    'modifier' => $modifierKey,
                    'provider_key' => $modifier['provider_key'],
                    'provider' => $modifier['provider'],
                    'value' => $modifier['value'],
                );
            }
        }

        foreach ($matchedModifiers as $matched) {

            if (!$matched['method'] instanceof RequestAlteringInterface) {
                continue;
            }

            $matched['method']->alterRequest($request, $matched['modifier']);
            $this->reinitializeRequest($request);
        }

        foreach ($matchedModifiers as $identifier) {
            $event = new ModifierMatchedEvent(
                $request,
                $identifier['provider_key'],
                $identifier['method'],
                $identifier['modifier'],
                $identifier['value']
            );
            $dispatcher->dispatch(PurlEvents::MODIFIER_MATCHED, $event);
            $this->matchedModifiers->add($event);
        }

        $request->attributes->set('purl.matched_modifiers', $matchedModifiers);
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
