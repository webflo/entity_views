<?php

/**
 * @file
 * Definition of Drupal\entity_views\Plugin\views\field\Link.
 */

namespace Drupal\entity_views\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler to present a link to the entity_link.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("entity_link")
 */
class Link extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }

  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['text'] = array('default' => '', 'translatable' => TRUE);
    return $options;
  }

  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['text'] = array(
      '#type' => 'textfield',
      '#title' => t('Text to display'),
      '#default_value' => $this->options['text'],
    );
    parent::buildOptionsForm($form, $form_state);

    // The path is set by renderLink function so don't allow to set it.
    $form['alter']['path'] = array('#access' => FALSE);
    $form['alter']['external'] = array('#access' => FALSE);
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->addAdditionalFields();
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    if ($entity = $this->getEntity($values)) {
      return $this->renderLink($entity, $values);
    }
  }

  /**
   * Prepares the link to view a entity.
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
    if ($entity->access('view')) {
      $this->options['alter']['make_link'] = TRUE;
      $this->options['alter']['path'] = $entity->getSystemPath();

      $text = !empty($this->options['text']) ? $this->options['text'] : t('view');
      return $text;
    }
  }

}
