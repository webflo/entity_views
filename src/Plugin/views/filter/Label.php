<?php

/**
 * @file
 * Definition of Drupal\entity_views\Plugin\views\filter\Label.
 */

namespace Drupal\entity_views\Plugin\views\filter;

use Drupal\Core\Entity\Entity;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Query\QueryInterface;

/**
 * Filter handler for entity ids.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("entity_views_label")
 */
class Label extends EntityId {

  protected $autocompleteRouteName = 'system.entity_autocomplete.labelid';

  /**
   * Maps a list of "@label (@id)" type strings to their respective entity ids.
   * Values which cannot be matched are omitted from the result array.
   */
  protected function extractIdsFromLabels($values) {
    $results = array();
    foreach ($values as $value) {
      // If the value is numeric, assume it's an id.
      if (is_numeric($value)) {
        $results[$value] = intval($value);
      }
      // If it's a string, try to parse the format "label (entity id)' and match
      // the id from the part in parenthesis.
      elseif (preg_match("/.+\((\d+)\)/", $value, $matches)) {
        $results[$value] = $matches[1];
      }
      elseif (preg_match("/.+\(([\w.]+)\)/", $value, $matches)) {
        $results[$value] = $matches[1];
      }
      if (empty($results[$value])) {
        // Try to get a match from the input string when the user didn't use the
        // autocomplete but filled in a value manually. We simply use the first
        // match, if we can find one.
        $entityType = \Drupal::entityManager()
          ->getDefinition($this->getEntityType());
        /** @var QueryInterface $query */
        $query = \Drupal::service('entity.autocomplete')
          ->getQuery($value, $entityType);

        $result = $query->range(0, 1)->execute();
        $results[$value] = reset($result);
      }
    }

    return $results;
  }

  /**
   * Gets the entity labels.
   */
  protected function getLabels(array $values) {
    $entity_labels = array();

    // Load those entities and loop through them to extract their labels.
    $entities = entity_load_multiple($this->getEntityType(), $values);

    /** @var EntityInterface $entity */
    foreach ($entities as $entity) {
      $entity_labels[] = \Drupal::service('entity.autocomplete')
        ->getEntityLabelWithId($entity);
    }
    return $entity_labels;
  }

}
