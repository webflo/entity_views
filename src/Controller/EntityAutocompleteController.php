<?php

/**
 * @file
 * Contains \Drupal\entity_views\Controller\EntityAutocompleteController.
 */

namespace Drupal\entity_views\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\entity_views\EntityAutocomplete;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Tests\Fixtures\EntityInterface;

class EntityAutocompleteController extends ControllerBase {

  /**
   * Constructs a new EntityAutocompleteController instance.
   *
   * @param \Drupal\entity_views\EntityAutocomplete $entity_autocomplete
   *   The entity autocomplete.
   */
  public function __construct(EntityAutocomplete $entity_autocomplete) {
    $this->entityAutocomplete = $entity_autocomplete;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity.autocomplete'));
  }

  /**
   * Returns response for the entity autocompletion.
   *
   * @param string $entity_type
   *   The entity type.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object containing the search string.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the autocomplete suggestions for existing
   *   entities.
   *
   * @see \Drupal\entity_views\EntityAutocomplete::getMatchesUuid()
   */
  public function autocompleteEntityUuid($entity_type, Request $request) {
    return new JsonResponse($this->entityAutocomplete->getMatchesUuid($request->query->get('q'), $entity_type, $request->query->get('page')));
  }

  /**
   * Returns response for the entity autocompletion.
   *
   * @param string $entity_type
   *   The entity type.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object containing the search string.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the autocomplete suggestions for existing
   *   entities.
   *
   * @see \Drupal\entity_views\EntityAutocomplete::getMatchesId()
   */
  public function autocompleteEntityId($entity_type, Request $request) {
    return new JsonResponse($this->entityAutocomplete->getMatchesId($request->query->get('q'), $entity_type, $request->query->get('page')));
  }

  /**
   * Returns response for the entity autocompletion.
   *
   * @param string $entity_type
   *   The entity type.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object containing the search string.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the autocomplete suggestions for existing
   *   entities.
   *
   * @see \Drupal\entity_views\EntityAutocomplete::getMatchesCombined()
   */
  public function autocompleteEntityLabelWithId($entity_type, Request $request) {
    return new JsonResponse($this->entityAutocomplete->getMatchesCombined($request->query->get('q'), $entity_type, $request->query->get('page')));
  }
}
