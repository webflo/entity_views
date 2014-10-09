<?php

/**
 * @file
 * Contains \Drupal\entity_views\EntityAutocomplete.
 */

namespace Drupal\entity_views;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;

class EntityAutocomplete {

  /**
   * The entity query factory service.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a UserAutocomplete object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
   *   The entity query factory.
   */
  public function __construct(EntityManagerInterface $entity_manager, QueryFactory $entity_query) {
    $this->entityQuery = $entity_query;
    $this->entityManager = $entity_manager;
  }

  /**
   * Get matches for the autocompletion of entity labels.
   *
   * @param string $string
   *   The string to match for usernames.
   * @param $entity_type_id
   *   The entity type.
   *
   * @return array
   *   An array containing the matching entities.
   */
  public function getMatches($string, $entity_type_id) {
    $matches = [];
    if ($string) {
      $entity_type = $this->entityManager->getDefinition($entity_type_id);
      $entity_storage = $this->entityManager->getStorage($entity_type_id);
      if (!($entity_label_key = $entity_type->getKey('label'))) {
        return [];
      }
      $entity_ids = $this->entityQuery->get($entity_type_id)
        ->condition($entity_label_key, $string, 'CONTAINS')
        ->accessCheck(TRUE)
        ->range(0, 10)
        ->execute();
      $entities = $entity_storage->loadMultiple($entity_ids);
      foreach ($entities as $entity) {
        /** @var \Drupal\Core\Entity\EntityInterface $entity */
        if ($entity->access('view')) {
          $matches[] = array(
            'value' => $entity->uuid(),
            'label' => $entity->label()
          );
        }
      }
    }

    return $matches;
  }
}
