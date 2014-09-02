<?php

/**
 * @file
 * Contains \Drupal\entity_views\Plugin\views\field\LinkEdit.
 */

namespace Drupal\entity_views\Plugin\views\field;

use Drupal\views\ResultRow;

/**
 * Field handler to present a link to entity edit.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("entity_link_edit")
 */
class LinkEdit extends Link {

  /**
     * Prepares the link to edit a entity.
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
    /**
     * @var \Drupal\Core\Entity\EntityInterface $entity
     */
    if ($entity && $entity->access('update')) {
      $this->options['alter']['make_link'] = TRUE;
      $this->options['alter']['path'] = $entity->getSystemPath('edit-form');
      $this->options['alter']['query'] = drupal_get_destination();

      $text = !empty($this->options['text']) ? $this->options['text'] : t('Edit');
      return $text;
    }
  }

}
