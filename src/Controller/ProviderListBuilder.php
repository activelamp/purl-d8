<?php

namespace Drupal\purl\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\purl\Plugin\ProviderManager;
use Drupal\purl\Plugin\MethodPluginManager;

/**
 * Provides a listing of PURL Provider.
 */
class ProviderListBuilder extends ConfigEntityListBuilder {

    public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
        return new static(
            $entity_type,
            $container->get('entity.manager')->getStorage($entity_type->id()),
            $container->get('purl.plugin.provider_manager'),
            $container->get('purl.plugin.method_manager')
        );
    }

    public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, ProviderManager $providerManager, MethodPluginManager $methodManager)
    {
        parent::__construct($entity_type, $storage);
        $this->providerManager = $providerManager;
        $this->methodManager = $methodManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildHeader() {
        $header['label'] = $this->t('Provider');
        $header['provider_key'] = $this->t('Provider Plugin');
        $header['method_key'] = $this->t('Method Plugin');
        return $header + parent::buildHeader();
    }

    /**
     * {@inheritdoc}
     */
    public function buildRow(EntityInterface $entity) 
    {
        $row['label'] = $this->getLabel($entity);
        
        $provider = $this->providerManager->getDefinition($entity->id());
        $method = $this->methodManager->getDefinition($entity->getMethodKey());

        $row['provider_key'] = $provider['label'];
        $row['method_key'] = $method['label'];

        // You probably want a few more properties here...

        return $row + parent::buildRow($entity);
    }

}
