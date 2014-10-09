<?php

/**
 * Implements hook_views_data_alter().
 */
function entity_views_views_data_alter(array &$data) {
  // Registers an entity area handler per entity type.
  foreach (\Drupal::entityManager()->getDefinitions() as $entity_type_id => $entity_type) {
    // Excludes entity types, which cannot be rendered.
    if ($entity_type->hasViewBuilderClass()) {
      $label = $entity_type->getLabel();
      $data['views']['entity_' . $entity_type_id] = array(
        'title' => t('Rendered entity - @label', array('@label' => $label)),
        'help' => t('Displays a rendered @label entity in an area.', array('@label' => $label)),
        'area' => array(
          'entity_type' => $entity_type_id,
          'id' => 'entity_views_entity',
        ),
      );
    }
  }
}
