<?php

namespace Drupal\migrate_files_and_images\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Files cross reference entities.
 */
class FilesCrossReferenceViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['files_cross_reference']['table']['base'] = [
      'field' => 'id',
      'title' => $this->t('Files cross reference'),
      'help' => $this->t('The Files cross reference ID.'),
    ];

    return $data;
  }

}
