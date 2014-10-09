<?php

/**
 * @file
 * Contains \Drupal\entity_views\Controller\EntityAutocompleteController.
 */

namespace Drupal\entity_views\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityAutocomplete;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class EntityAutocompleteController extends ControllerBase {

  /**
   * Constructs a new EntityAutocompleteController instance.
   *
   * @param \Drupal\Core\Entity\EntityAutocomplete $entity_autocomplete
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
   * @see \Drupal\Core\Entity\EntityAutocomplete::getMatches()
   */
  public function autocompleteEntity($entity_type, Request $request) {
    $matches = $this->entityAutocomplete->getMatches($request->query->get('q'), $entity_type);

    return new JsonResponse($matches);
  }

}
