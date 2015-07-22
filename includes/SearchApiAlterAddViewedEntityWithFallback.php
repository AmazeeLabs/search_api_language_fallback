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

}
