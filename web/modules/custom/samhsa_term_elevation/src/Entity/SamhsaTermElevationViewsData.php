<?php

namespace Drupal\samhsa_term_elevation\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for SAHMSA Term Elevation entities.
 */
class SamhsaTermElevationViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
