<?php

/**
 * @file
 * Definition of Drupal\entity_views\Plugin\views\field\Entity.
 */

namespace Drupal\entity_views\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler to present a bundle to the entity_bundle.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("entity_view")
 */
class Entity extends FieldPluginBase {

  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['display'] = array('default' => 'label');
    $options['link_to_entity']['default'] = TRUE;
    $options['view_mode'] = array('default' => 'default');
    $options['bypass_access'] = array('default' => FALSE);

    return $options;
  }

  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $options = array(
      'label' => t('Show entity label'),
      'id' => t('Show entity ID'),
      'view' => t('Show complete entity'),
    );

    $form['display'] = array(
      '#type' => 'select',
      '#title' => t('Display'),
      '#description' => t('Decide how this field will be displayed.'),
      '#options' => $options,
      '#default_value' => $this->options['display'],
    );

    $form['link_to_entity'] = array(
      '#type' => 'checkbox',
      '#title' => t('Link to entity'),
      '#description' => t('Link this field to the entity.'),
      '#default_value' => $this->options['link_to_entity'],
      '#states' => array(
        'invisible' => array(
          ':input[name="options[display]"]' => array('value' => 'view'),
        ),
      ),
    );

    // Stolen from entity_views_plugin_row_entity_view.
    $view_modes = array();
    foreach (\Drupal::entityManager()
               ->getViewModes($this->getEntityType()) as $mode => $settings) {
      $view_modes[$mode] = $settings['label'];
    }

    if (count($view_modes) > 1) {
      $form['view_mode'] = array(
        '#type' => 'select',
        '#options' => $view_modes,
        '#title' => t('View mode'),
        '#default_value' => $this->options['view_mode'],
        '#states' => array(
          'visible' => array(
            ':input[name="options[display]"]' => array('value' => 'view'),
          ),
        ),
      );
    }
    else {
      $form['view_mode'] = array(
        '#type' => 'value',
        '#value' => 'default',
      );
    }

    $form['bypass_access'] = array(
      '#type' => 'checkbox',
      '#title' => t('Bypass access checks'),
      '#description' => t('If enabled, access permissions for rendering the entity are not checked.'),
      '#default_value' => !empty($this->options['bypass_access']),
    );
  }

  /**
   * Renders the field.
   *
   * @param \Drupal\views\ResultRow $values
   *   The values retrieved from a single row of a view's query result.
   */
  public function render(ResultRow $values) {
    $entity = $this->getEntity($values);
    return entity_view($entity, $this->options['view_mode']);
  }

}
