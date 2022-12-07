<?php

namespace Drupal\samhsa_pep_migrate_custom;

use Drupal\Core\Link;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
// Use Drupal\Core\Routing\LinkGeneratorTrait;.
use Drupal\Core\Url;

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
    /** @var \Drupal\samhsa_pep_migrate_custom\Entity\UnconvertedLinks $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::fromTextAndUrl(
      $entity->label(),
      new Url(
        'entity.unconverted_links.edit_form', [
          'unconverted_links' => $entity->id(),
        ]
      )
    );
    return $row + parent::buildRow($entity);
  }

}
