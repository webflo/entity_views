<?php

/**
 * @file
 * Definition of Drupal\entity_views\Plugin\views\filter\EntityId.
 */

namespace Drupal\entity_views\Plugin\views\filter;

use Drupal\Component\Utility\Tags;
use Drupal\Core\Entity\Entity;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\InOperator;

/**
 * Filter handler for entity ids.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("entity_views_id")
 */
class EntityId extends InOperator {

  protected $alwaysMultiple = TRUE;
  protected $validatedExposedInput;
  protected $autocompleteRouteName = 'system.entity_autocomplete.id';

  /**
   * {@inheritdoc}
   *
   * Take input from exposed handlers and assign to this handler, if necessary.
   */
  function acceptExposedInput($input) {
    $rc = parent::acceptExposedInput($input);

    if ($rc) {
      // If we have previously validated input, override.
      if (isset($this->validatedExposedInput)) {
        $this->value = $this->validatedExposedInput;
      }
    }

    return $rc;
  }

  /**
   * {@inheritdoc}
   *
   * Validate the exposed handler form
   */
  function validateExposed(&$form, FormStateInterface $form_state) {
    if (empty($this->options['exposed']) || empty($this->options['expose']['identifier'])) {
      return;
    }

    $identifier = $this->options['expose']['identifier'];
    $input = $form_state->getValue($identifier);

    if ($this->options['is_grouped'] && isset($this->options['group_info']['group_items'][$input])) {
      $this->operator = $this->options['group_info']['group_items'][$input]['operator'];
      $input = $this->options['group_info']['group_items'][$input]['value'];
    }

    if (!$this->options['is_grouped'] || ($this->options['is_grouped'] && ($input != 'All'))) {
      $ids = $this->validate_id_values($form[$identifier], $form_state, Tags::explode($input));
    }
    else {
      $ids = FALSE;
    }

    if ($ids) {
      $this->validatedExposedInput = $ids;
    }
  }

  /**
   * Checks that all ids specified in $values exist and raises a form error in
   * case of error.
   */
  function validate_id_values(&$element, FormStateInterface $form_state, array $values) {
    // Extract entity ids from list of combined entity labels.
    $values = $this->extractIdsFromLabels($values);

    $args = array();
    $missing = array();
    foreach ($values as $value => $id) {
      if ($id) {
        $missing[$id] = $value;
        $args[] = intval($id);
      }
      else {
        $missing[$value] = $value;
      }
    }

    if (empty($args)) {
      return array();
    }

    $ids = array();
    $result = entity_load_multiple($this->getEntityType(), $args);
    /** @var EntityInterface $entity */
    foreach ($result as $entity) {
      unset($missing[$entity->id()]);
      $ids[] = $entity->id();
    }

    if (!empty($missing)) {
      $form_state->setError($element, \Drupal::translation()
        ->formatPlural(count($missing),
          'Unable to find entity: @ids',
          'Unable to find entities: @ids',
          array('@ids' => implode(', ', array_keys($missing)))));
    }

    return $ids;
  }

  /**
   * Maps a list of "@label (@id)" type strings to their respective entity ids.
   * Values which cannot be matched are omitted from the result array.
   */
  protected function extractIdsFromLabels($values) {
    $results = array();
    foreach ($values as $value) {
      // If the value is numeric, assume it's an id.
      if (is_numeric($value)) {
        $results[$value] = intval($value);
      }
    }
    return $results;
  }

  /**
   * {@inheritdoc}
   *
   * Autocomplete fields do not have any value options.
   */
  public function getValueOptions() {
    return;
  }

  /**
   * {@inheritdoc}
   *
   * Display the filter on the administrative summary.
   */
  public function adminSummary() {
    // Set up $this->valueOptions for adminSummary() so we don't get any
    // validation errors.
    if (!is_array($this->value)) {
      $this->value = Tags::explode($this->value);
    }
    $this->valueOptions = array_combine($this->value, $this->value);
    return parent::adminSummary();
  }

  /**
   * {@inheritdoc}
   *
   * Perform any necessary changes to the form values prior to storage.
   */
  protected function valueSubmit($form, FormStateInterface $form_state) {
    parent::valueSubmit($form, $form_state);

    // Make sure we store arrays instead of comma-separated values.
    $value = $form_state->getValue(array('options', 'value'));
    $value = Tags::explode($value);
    $ids = $this->extractIdsFromLabels($value);
    $value = array();
    foreach ($ids as $id) {
      empty($id) ?: $value[] = intval($id);
    }
    $form_state->setValue(array('options', 'value'), $value);
  }

  /**
   * {@inheritdoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    $values = $this->value ? $this->getLabels($this->value) : array();

    sort($values);
    $default_value = Tags::implode($values);
    $form['value'] = array(
      '#type' => 'textfield',
      '#maxlength' => 99999, // Disable textfield default of 128 length.
      '#title' => $this->t('Entity Labels'),
      '#description' => $this->t('Enter a comma separated list of entity labels.'),
      '#default_value' => $default_value,
      '#autocomplete_route_name' => $this->autocompleteRouteName,
      '#autocomplete_route_parameters' => array('entity_type' => $this->getEntityType()),
    );

    $user_input = $form_state->getUserInput();
    if ($form_state->get('exposed') && !isset($user_input[$this->options['expose']['identifier']])) {
      $user_input[$this->options['expose']['identifier']] = $default_value;
      $form_state->setUserInput($user_input);
    }
  }

  /**
   * Gets the entity labels.
   */
  protected function getLabels(array $values) {
    // We just display entity ids.
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  protected function valueValidate($form, FormStateInterface $form_state) {
    $values = Tags::explode($form_state->getValue(array('options', 'value')));
    if ($ids = $this->validate_id_values($form['value'], $form_state, $values)) {
      $form_state->setValue(array('options', 'value'), $ids);
    }
  }

}
