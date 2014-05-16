<?php

/**
 * @file
 * Contains \Drupal\entity_views\Plugin\views\field\LinkDelete.
 */

namespace Drupal\entity_views\Plugin\views\field;

use Drupal\node\Plugin\views\field\Link;
use Drupal\views\ResultRow;

/**
 * Field handler to present a link to delete a node.
 *
 * @ingroup views_field_handlers
 *
 * @PluginID("entity_link_delete")
 */
class LinkDelete extends Link {

  /**
   * Prepares the link to delete a entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The node entity this field belongs to.
   * @param \Drupal\views\ResultRow $values
   *   The values retrieved from the view's result set.
   *
   * @return string
   *   Returns a string for the link text.
   */
  protected function renderLink($entity, ResultRow $values) {
    // Ensure user has access to delete this node.
    if ($entity && $entity->access('delete')) {
      $this->options['alter']['make_link'] = TRUE;
      $this->options['alter']['path'] = $entity->getSystemPath('delete-form');
      $this->options['alter']['query'] = drupal_get_destination();

      $text = !empty($this->options['text']) ? $this->options['text'] : t('delete');
      return $text;
    }
  }

}
