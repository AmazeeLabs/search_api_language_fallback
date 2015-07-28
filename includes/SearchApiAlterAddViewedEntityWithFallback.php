<?php

/**
 * @file
 * Contains SearchApiAlterAddViewedEntityWithFallback.
 */

/**
 * Overrides "Complete entity view" alter callback to support language fallback.
 */
class SearchApiAlterAddViewedEntityWithFallback extends SearchApiAlterAddViewedEntity {

  /**
   * {@inheritdoc}
   */
  public function alterItems(array &$items) {
    $entity_type = $this->index->getEntityType();
    $languages = language_list();

    // Save languages.
    $language_interface = $GLOBALS[LANGUAGE_TYPE_INTERFACE];
    $language_content = $GLOBALS[LANGUAGE_TYPE_CONTENT];

    foreach ($items as $key => $item) {
      try {

        // The $items array can contain the same entities. Clone them to break
        // references and avoid overrides.
        $items[$key] = $item = clone $item;

        // Override global languages because language_fallback module uses them.
        if ($item->language === LANGUAGE_NONE) {
          $GLOBALS[LANGUAGE_TYPE_INTERFACE] = $language_interface;
          $GLOBALS[LANGUAGE_TYPE_CONTENT] = $language_content;
        }
        else {
          $GLOBALS[LANGUAGE_TYPE_INTERFACE] = $languages[$item->language];
          $GLOBALS[LANGUAGE_TYPE_CONTENT] = $languages[$item->language];
        }

        // Clear static caches that affect the resulting render array.
        drupal_static_reset('field_language');
        drupal_static_reset('language_fallback_get_candidates');

        // Exclude items with empty title if required.
        if (!empty($this->options['exclude_empty_title']) && isset($item->title_field)) {
          // First get the field language, then try to get field items with this
          // language. This guarantees correct work if language fallback is
          // used.
          // Also, it's important to do this after static caches are cleared and
          // global languages are replaced.
          $field_language = field_language($entity_type, $item, 'title_field', $item->language);
          if (field_get_items($entity_type, $item, 'title_field', $field_language) === FALSE) {
            unset($items[$key]);
            continue;
          }
        }

        $_items = array(&$item);
        parent::alterItems($_items);
      }
      catch (Exception $e) { }

      // Restore original languages.
      $GLOBALS[LANGUAGE_TYPE_INTERFACE] = $language_interface;
      $GLOBALS[LANGUAGE_TYPE_CONTENT] = $language_content;
    }
  }

  /**
   * Returns an entity view mode used for "Complete entity view".
   *
   * @return string|null
   */
  public function getViewMode() {
    return isset($this->options['mode']) ? $this->options['mode'] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function configurationForm() {
    $form = parent::configurationForm();
    $this->options += array(
      'exclude_empty_title' => FALSE,
    );
    $form['exclude_empty_title'] = array(
      '#type' => 'checkbox',
      '#title' => t('Exclude entities with empty title_field'),
      '#description' => t('This works only on entities having title_field field.'),
      '#default_value' => $this->options['exclude_empty_title'],
    );
    return $form;
  }

}
