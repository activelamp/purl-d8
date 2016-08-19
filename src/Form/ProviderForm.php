<?php

namespace Drupal\purl\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityForm;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;

class ProviderForm extends EntityForm {
  
  /**
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
   *   The entity query.
   */
  public function __construct(QueryFactory $entity_query) {
    $this->entityQuery = $entity_query;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('entity.query')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state)
  {
    $form = parent::form($form, $form_state);

    $provider = $this->entity;

    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $provider->getLabel(),
      '#description' => $this->t("Label for the provider."),
      '#required' => TRUE,
    );

    $form['provider_key'] = array(
      '#type' => 'machine_name',
      '#default_value' => $example->getProviderKey(),
      '#machine_name' => array(
        'exists' => array($this, 'exist'),
      ),
      '#disabled' => true
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $example = $this->entity;

    $status = $example->save();

    if ($status) {
      drupal_set_message($this->t('Saved the %label provider.', array(
        '%label' => $example->label(),
      )));
    }
    else {
      drupal_set_message($this->t('The %label provider was not saved.', array(
        '%label' => $example->label(),
      )));
    }

    $form_state->setRedirect('entity.purl_provider.collection');
  }

  public function exist($id) {
    $entity = $this->entityQuery->get('purl_provider')
      ->condition('provider_key', $id)
      ->execute();
    return (bool) $entity;
  }
}
