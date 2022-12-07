<?php

namespace Drupal\samhsa_term_elevation;

use Drupal;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of SAHMSA Term Elevation entities.
 *
 * @ingroup samhsa_term_elevation
 */
class SamhsaTermElevationListBuilder extends EntityListBuilder {

  protected $websitesBases = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage) {
    parent::__construct($entity_type, $storage);
    $this->websitesBases = Drupal::service('samhsa_te_solr_connections')
      ->getWebsitesBases();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds() {
    $query = $this->getStorage()->getQuery()->sort('query');
    if ($this->limit) {
      $query->pager($this->limit);
    }
    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    // $header['id'] = $this->t('ID');
    $header['query'] = $this->t('Query');
    $header['elnid'] = $this->t('Include');
    $header['exnid'] = $this->t('Exclude');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\samhsa_term_elevation\Entity\SamhsaTermElevation $entity */
    // $row['id'] = $entity->id();
    $row['query'] = Link::createFromRoute($entity->label(), 'entity.samhsa_term_elevation.edit_form', ['samhsa_term_elevation' => $entity->id()]);
    $row['elnid']['data'] = $this->buildLinksToContent($entity->getIncluded(), 'el');
    $row['exnid']['data'] = $this->buildLinksToContent($entity->getExcluded(), 'ex');
    return $row + parent::buildRow($entity);
  }

  /**
   * Generate links to the content.
   *
   * @param array $elevates
   *   Identifiers of all elevated content.
   * @param $type
   *
   * @return array
   */
  private function buildLinksToContent($elevates, $type) {
    $links = [];
    foreach ($elevates as $title) {
      $id = _samhsa_term_elevation_extract_id($title);
      if ($p = strpos($id, '-')) {
        $hash = substr($id, 0, $p);
        if ($site = $this->websitesBases[$hash]) {
          preg_match('/node\/(.*?):/', $id, $matches);
          if (substr($site, -1) == '/') {
            $string = "{$site}node/{$matches[1]}";
          }
          else {
            $string = "{$site}/node/{$matches[1]}";
          }
          $url = Url::fromUri($string);
          $links[] = Link::fromTextAndUrl($title, $url)->toString();
        }
        else {
          $links[] = $this->t('(Broken link) %title', ['%title' => $title]);
        }

      }
    }

    $fieldset_id = 'te_' . $type . '_' . $id;
    $content = [];
    $content[$fieldset_id] = [
      '#type' => 'details',
      '#title' => 'Content',
      '#closed' => TRUE,
    ];
    $content[$fieldset_id][$type . '_link_' . $key] = [
      '#theme' => 'item_list',
      '#items' => $links,
      '#list_type' => 'ul',
    ];
    return $content;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['table']['#id'] = 'te-elevated-query-collection';
    $build['search'] = [
      '#id' => 'search-query',
      '#type' => 'textfield',
      '#title' => t('Search query'),
      '#weight' => -100,
    ];
    $build['#attached']['library'][] = 'samhsa_term_elevation/samhsa-term-elevation-query-filter';
    return $build;
  }

}
