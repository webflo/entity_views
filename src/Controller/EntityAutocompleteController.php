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
   * @see \Drupal\entity_views\EntityAutocomplete::getMatchesId()
   */
  public function autocomplete($entity_type, $label_callback, $value_callback, Request $request) {
    return new JsonResponse($this->entityAutocomplete->getMatches(
      $request->query->get('q'),
      $entity_type,
      $value_callback,
      $label_callback,
      $request->query->get('page')));
  }
}
