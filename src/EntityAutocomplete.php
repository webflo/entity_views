<?php
/**
 * @file
 * Contains \Drupal\entity_views\EntityAutocomplete.
 */

namespace Drupal\entity_views;

use Drupal\Component\Utility\String;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
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
   * Constructs a EntityAutocomplete object.
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
   * Returns the uuid of the given entity.
   */
  public static function getEntityUuid(EntityInterface $entity) {
    return $entity->uuid();
  }

  /**
   * Returns the id of the given entity.
   */
  public static function getEntityId(EntityInterface $entity) {
    return $entity->id();
  }

  /**
   * Returns the label and id of the given entity as "@label (@id)".
   */
  public static function getEntityLabelWithId(EntityInterface $entity) {
    return String::format('!label (!id)', array(
      '!label' => $entity->label(),
      '!id' => $entity->id(),
    ));
  }

  /**
   * Returns the label of the given entity.
   */
  public static function getEntityLabel(EntityInterface $entity) {
    return $entity->label();
  }

  /**
   * Get matches for the autocompletion of entity labels.
   *
   * The number of results is limited to 10.
   *
   * @param string $string
   *   The string to match for entity labels.
   * @param $entity_type_id
   *   The entity type.
   * @param callable $value_callback
   * @param callable $label_callback
   * @param int $page
   *   The results page number.
   *
   * @return array
   *   An array containing the matching entities.
   */
  public function getMatches($string, $entity_type_id, callable $value_callback, callable $label_callback, $page = 1) {
    $matches = [];
    if ($string) {
      $entity_type = $this->entityManager->getDefinition($entity_type_id);
      $entity_storage = $this->entityManager->getStorage($entity_type_id);
      $query = $this->getQuery($string, $entity_type);

      // Inject paging information: JS assumes that it gets only 10 results; if
      // we have we more than 10 results, we return an eleventh result to
      // indicate that more can be loaded.
      $page = $page < 1 ? 1 : $page;
      $query->range(($page - 1) * 10, 11);

      // Load entities and match each into a value/label pair.
      $entities = $entity_storage->loadMultiple($query->execute());
      foreach ($entities as $entity) {
        /** @var \Drupal\Core\Entity\EntityInterface $entity */
        if ($entity->access('view')) {
          $matches[] = $this->getMatchEntry($entity, $value_callback, $label_callback);
        }
      }
    }

    return $matches;
  }

  /**
   * Get the query for finding entities matching the given search string.
   *
   * @param $string
   *   The string to match for entity labels.
   * @param EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @throws \InvalidArgumentException
   *   Thrown when the entity type does not have a label property to match the
   *   search string against.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   The query used to find matching entities.
   */
  public function getQuery($string, EntityTypeInterface $entity_type) {
    if (!($entity_label_key = $entity_type->getKey('label'))) {
      throw new \InvalidArgumentException(String::format('Cannot create auto-complete query for %type, because it does not have a label key.', array(
        '%type' => $entity_type->getLabel(),
      )));
    }
    return $this->entityQuery->get($entity_type->id())
      ->condition($entity_label_key, $string, 'CONTAINS')
      ->accessCheck(TRUE);
  }

  /**
   * Build a value-label pair for the given entity, using the supplied
   * callbacks.
   *
   * @param EntityInterface $entity
   *   The entity to build the entry for.
   * @param callable $value_callback
   *   The value callback.
   * @param callable $label_callback
   *   The label callback.
   * @return array
   *   An array with at least two keys: 'value' and 'label'.
   */
  public function getMatchEntry(EntityInterface $entity, callable $value_callback, callable $label_callback) {
    return array(
      'value' => call_user_func($value_callback, $entity),
      'label' => call_user_func($label_callback, $entity),
    );
  }
}
