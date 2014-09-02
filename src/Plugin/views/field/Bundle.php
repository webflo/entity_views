<?php

/**
 * @file
 * Definition of Drupal\entity_views\Plugin\views\field\Bundle.
 */

namespace Drupal\entity_views\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\Plugin\views\field\MachineName;
use Drupal\views\ResultRow;

/**
 * Field handler to present a bundle to the entity_bundle.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("entity_bundle")
 */
class Bundle extends MachineName {

  public function getValueOptions() {
    if (isset($this->valueOptions)) {
      return;
    }

    $bundles = \Drupal::entityManager()->getBundleInfo($this->configuration['entity type']);
    $this->valueOptions = array();
    foreach ($bundles as $name => $info) {
      $this->valueOptions[$name] = $info['label'];
    }
  }

}
