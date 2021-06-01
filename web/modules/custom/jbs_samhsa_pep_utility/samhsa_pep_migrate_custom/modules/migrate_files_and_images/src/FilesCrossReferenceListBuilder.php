<?php

/**
 * @file
 * Contains \Drupal\migrate_files_and_images\FilesCrossReferenceListBuilder.
 */

namespace Drupal\migrate_files_and_images;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
//use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Files cross reference entities.
 *
 * @ingroup migrate_files_and_images
 */
class FilesCrossReferenceListBuilder extends EntityListBuilder {
  //use LinkGeneratorTrait;
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Files cross reference ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\migrate_files_and_images\Entity\FilesCrossReference */
    $row['id'] = $entity->id();
    $row['name'] = $this->l(
      $entity->label(),
      new Url(
        'entity.files_cross_reference.edit_form', array(
          'files_cross_reference' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
