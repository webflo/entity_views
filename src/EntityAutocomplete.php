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
   * Get matches for the autocompletion of entity labels, using UUIDs as values.
   *
   * @param string $string
   *   The string to match for usernames.
   * @param $entity_type_id
   *   The entity type.
   *
   * @return array
   *   An array containing the matching entities.
   */
  public function getMatchesUuid($string, $entity_type_id) {
    return $this->getMatches($string, $entity_type_id,
      array(__CLASS__, 'getEntityUuid'), array(__CLASS__, 'getEntityLabel'));
  }

  /**
   * Get matches for the autocompletion of entity labels.
   *
   * @param string $string
   *   The string to match for entity labels.
   * @param $entity_type_id
   *   The entity type.
   * @param callable $value_callback
   * @param callable $label_callback
   *
   * @return array
   *   An array containing the matching entities.
   */
  public function getMatches($string, $entity_type_id, callable $value_callback, callable $label_callback) {
    $matches = [];
    if ($string) {
      $entity_type = $this->entityManager->getDefinition($entity_type_id);
      $entity_storage = $this->entityManager->getStorage($entity_type_id);
      $entity_ids = $this->getQuery($string, $entity_type)->execute();
      $entities = $entity_storage->loadMultiple($entity_ids);
      foreach ($entities as $entity) {
        /** @var \Drupal\Core\Entity\EntityInterface $entity */
        if ($entity->access('view')) {
          $matches[] = array(
            'value' => call_user_func($value_callback, $entity),
            'label' => call_user_func($label_callback, $entity),
          );
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
      ->accessCheck(TRUE)
      ->range(0, 10);
  }

  /**
   * Get matches for the autocompletion of entity labels, using a combination of
   * the label and the id as values.
   *
   * The value format is "@label (@id)".
   *
   * @param string $string
   *   The string to match for usernames.
   * @param $entity_type_id
   *   The entity type.
   *
   * @return array
   *   An array containing the matching entities.
   */
  public function getMatchesCombined($string, $entity_type_id) {
    return $this->getMatches($string, $entity_type_id,
      array(__CLASS__, 'getEntityLabelWithId'),
      array(__CLASS__, 'getEntityLabel'));
  }

  /**
   * Get matches for the autocompletion of entity labels, using IDs as values.
   *
   * @param string $string
   *   The string to match for usernames.
   * @param $entity_type_id
   *   The entity type.
   *
   * @return array
   *   An array containing the matching entities.
   */
  public function getMatchesId($string, $entity_type_id) {
    return $this->getMatches($string, $entity_type_id,
      array(__CLASS__, 'getEntityId'), array(__CLASS__, 'getEntityLabel'));
  }
}
