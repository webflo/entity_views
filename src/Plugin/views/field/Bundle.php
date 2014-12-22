<?php

/**
 * @file
 * Definition of Drupal\entity_views\Plugin\views\field\Bundle.
 */

namespace Drupal\entity_views\Plugin\views\field;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\Plugin\views\field\MachineName;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;

/**
 * Field handler to present a bundle to the entity_bundle.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("entity_bundle")
 */
class Bundle extends MachineName {

  /**
   * The entity type for the filter.
   *
   * @var string
   */
  protected $entityTypeId;

  /**
   * The entity type definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $entityType;

  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);

    $this->entityTypeId = $this->getEntityType();
    $this->entityType = \Drupal::entityManager()->getDefinition($this->entityTypeId);
  }

  public function getValueOptions() {
    if (isset($this->valueOptions)) {
      return;
    }

    $bundles = \Drupal::entityManager()->getBundleInfo($this->entityTypeId);
    $this->valueOptions = array();
    foreach ($bundles as $name => $info) {
      $this->valueOptions[$name] = $info['label'];
    }
  }

}
