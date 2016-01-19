<?php

namespace Drupal\purl\Event;

use Drupal\purl\Plugin\MethodPluginManager;
use Drupal\purl\Plugin\ProviderManager;
use Drupal\purl\Plugin\ModifierIndex;
use Drupal\purl\Plugin\Purl\Method\RequestAlteringInterface;
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
     * @var MethodManager
     */
    protected $methodManager;

    /**
     * @var ProviderManager
     */
    protected $providerManager;

    /**
     * @var ModifierIndex
     */
    protected $modifierIndex;


    public function __construct(
        ModifierIndex $modifierIndex,
        ProviderManager $providerManager,
        MethodPluginManager $methodManager
    ) {
        $this->modifierIndex = $modifierIndex;
        $this->providerManager = $providerManager;
        $this->methodManager = $methodManager;
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
        $modifiers = $this->modifierIndex->findModifiers();

        // This should no longer be necessary once caching issue in
        // ProviderManager is resolved.
        foreach ($modifiers as $i => $modifier) {
            $config = $this->getProviderConfig($modifier['provider']);
            $modifiers[$i]['method'] = $config['method'];
        }

        return $modifiers;
    }

    /**
     * This should no longer be necessary once caching issue in
     * ProviderManager is resolved.
     *
     * @return array
     */
    protected function getProviderConfig($id)
    {
        if (!isset($this->providerConfigs[$id])) {
            $this->providerConfigs[$id] = $this->providerManager->getProviderConfiguration($id);
        }
        return $this->providerConfigs[$id];
    }


    public function onRequest(GetResponseEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $request = $event->getRequest();
        $modifiers = $this->getModifiers();

        $matchedModifiers = array();
        $requestAlteringMethods = array();

        foreach ($modifiers as $modifier) {

            $providerConfig = $this->getProviderConfig($modifier['provider']);
            $methodKey = $providerConfig['method'];
            $modifierKey = $modifier['modifier'];

            if (!$this->methodManager->hasMethodPlugin($methodKey)) {
                continue;
            }

            $methodPlugin = $this->methodManager->getMethodPlugin($methodKey);
            $contains = $methodPlugin->contains($request, $modifierKey);

            if ($contains) {

                $matchedModifiers[] = array(
                    "method_plugin" => $methodPlugin,
                    "modifier" => $modifierKey,
                );

                if ($method instanceof RequestAlteringInterface) {
                    $requestAlteringMethods[] = $method;
                }
            }
        }

        foreach ($requestAlteringMethods as $method) {
            $method["plugin"]->alterRequest($request, $method["modifier"]);
            $this->reinitializeRequest($request);
        }

        foreach ($matchedModifiers as $identifier) {
            $dispatcher->dispatch(PurlEvents::MODIFIER_MATCHED, new ModifierMatchedEvent(
                $request,
                $identifier['provider'],
                $identifier['modifier'],
                $identifier['value']
            ));
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
