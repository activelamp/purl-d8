<?php

namespace Drupal\purl\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\purl\Plugin\ProviderManager;
use Drupal\purl\Plugin\MethodPluginManager;
use Drupal\Core\Entity\EntityForm;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;

class ProviderForm extends EntityForm
{

    /**
     * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
     *   The entity query.
     */
    public function __construct(QueryFactory $entity_query, ProviderManager $providerManager, MethodPluginManager $methodManager) {
        $this->entityQuery = $entity_query;
        $this->providerManager = $providerManager;
        $this->methodManager = $methodManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('entity.query'),
            $container->get('purl.plugin.provider_manager'),
            $container->get('purl.plugin.method_manager')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function form(array $form, FormStateInterface $form_state)
    {
        $form = parent::form($form, $form_state);

        $provider = $this->entity;

        $providerOptions = [];
        $methodOptions = [];

        foreach ($this->providerManager->getDefinitions() as $id => $definition) {
            $providerOptions[$id] = $definition['label'];
        }

        foreach ($this->methodManager->getDefinitions() as $id => $definition) {
            $methodOptions[$id] = $definition['label'];
        }


        $form['label'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Label'),
            '#maxlength' => 255,
            '#default_value' => $provider->getLabel(),
            '#description' => $this->t("Label for the provider."),
            '#required' => TRUE,
        );

        $form['provider_key'] = array(
            '#type' => 'select',
            '#title' => $this->t('Provider Plugin'),
            '#default_value' => $provider->getProviderKey(),
            '#options' => $providerOptions,
            '#disabled' => !$provider->isNew()
        );

        $form['method_key'] = array(
            '#type' => 'select',
            '#title' => $this->t('Method Plugin'),
            '#default_value' => $provider->getMethodKey(),
            '#options' => $methodOptions,
        );


        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function save(array $form, FormStateInterface $form_state)
    {
        $provider = $this->entity;

        $status = $provider->save();

        if ($status) {
            drupal_set_message($this->t('Saved the %label provider.', array(
                '%label' => $provider->getLabel(),
            )));
        }
        else {
            drupal_set_message($this->t('The %label provider was not saved.', array(
                '%label' => $provider->getLabel(),
            )));
        }

        $form_state->setRedirect('entity.purl_provider.collection');
    }

    public function exist($id)
    {
        $entity = $this->entityQuery->get('purl_provider')
            ->condition('provider_key', $id)
            ->execute();
        return (bool) $entity;
    }
}
