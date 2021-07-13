<?php

/**
 * @file
 * Contains \Drupal\samhsa_pep_migrate_custom\UnconvertedLinksListBuilder.
 */

namespace Drupal\samhsa_pep_migrate_custom;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
#use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;
use Drupal\Core\Link;
/**
 * Defines a class to build a listing of Unconverted links entities.
 *
 * @ingroup samhsa_pep_migrate_custom
 */
class UnconvertedLinksListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Unconverted links ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\samhsa_pep_migrate_custom\Entity\UnconvertedLinks */
    $row['id'] = $entity->id();
    $row['name'] = \Drupal\Core\Link::fromTextAndUrl(
      $entity->label(),
      new Url(
        'entity.unconverted_links.edit_form', array(
          'unconverted_links' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
