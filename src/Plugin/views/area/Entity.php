<?php

/**
 * @file
 * Contains \Drupal\entity_views\Plugin\views\area\Entity.
 */

namespace Drupal\entity_views\Plugin\views\area;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\area\Entity as EntityBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an area handler which renders an entity in a certain view mode.
 *
 * @ingroup views_area_handlers
 *
 * @ViewsArea("entity_views_entity")
 */
class Entity extends EntityBase {

 /**
   * Constructs a new Entity instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManagerInterface $entity_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['entity_id'] = array(
      '#title' => t('ID / UUID'),
      '#description' => $this->t('You can put in either the entity ID or the UUID'),
      '#type' => 'textfield',
      '#default_value' => $this->options['entity_id'],
      '#autocomplete_route_name' => 'system.entity_autocomplete.uuid',
      '#autocomplete_route_parameters' => ['entity_type' => $this->entityType],
    );
  }

  /**
   * {@inheritdoc}
   */
  public function submitOptionsForm(&$form, FormStateInterface $form_state, &$options = []) {
    parent::submitOptionsForm($form, $form_state);

    if ($entity = $this->entityManager->getStorage($this->entityType)->load($options['entity_id'])) {
      $options['entity_id'] = $entity->uuid();
    }
  }

  /**
   * {@inheritdoc}
   */
   public function render($empty = FALSE) {
     if (!$empty || !empty($this->options['empty'])) {
      $entity_id_or_uuid = $this->tokenizeValue($this->options['entity_id']);

      $entity_storage = $this->entityManager->getStorage($this->entityType);
      $view_builder = $this->entityManager->getViewBuilder($this->entityType);
      // Try to load by ID and then by UUID.
      if (($entity = $entity_storage->load($entity_id_or_uuid)) || (($entities = $entity_storage->loadByProperties(['uuid' => $entity_id_or_uuid])) && ($entity = reset($entities)))) {
        if (!empty($this->options['bypass_access']) || $entity->access('view')) {
          return $view_builder->view($entity, $this->options['view_mode']);
        }
       }
     }
   }

}
