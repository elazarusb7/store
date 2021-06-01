<?php

/**
 * @file
 * Contains \Drupal\samhsa_pep_migrate_custom\Entity\UnconvertedLinks.
 */

namespace Drupal\samhsa_pep_migrate_custom\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Unconverted links entities.
 */
class UnconvertedLinksViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['unconverted_links']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Unconverted links'),
      'help' => $this->t('The Unconverted links ID.'),
    );

    return $data;
  }

}
