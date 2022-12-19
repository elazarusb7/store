<?php

namespace Drupal\samhsa_term_elevation\Controller;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Theme\Registry;
use Drupal\Core\Url;

/**
 * Class DefaultViewerController.
 *
 * @package Drupal\samhsa_term_elevation\Controller
 */
class DefaultViewerController extends EntityViewBuilder {

  /**
   * Main domain for all websites in the multi-indexed core.
   *
   * @var array
   */
  protected $websitesBases = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeInterface $entity_type,
                              EntityRepositoryInterface $entity_repository,
                              LanguageManagerInterface $language_manager,
                              Registry $theme_registry = NULL,
                              EntityDisplayRepositoryInterface $entity_display_repository = NULL) {
    parent::__construct($entity_type, $entity_repository, $language_manager, $theme_registry, $entity_display_repository);
    $this->websitesBases = \Drupal::service('samhsa_te_solr_connections')->getWebsitesBases();
  }

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {

    // $build['include'] = $this->buildLinksToContentTable($entity->getIncluded(), $this->t('Elevated content'));
    //    $build['exclude'] = $this->buildLinksToContentTable($entity->getExcluded(), $this->t('Excluded content'));
    $build['include_details'] = [
      '#type' => 'details',
      '#title' => $this->t('Elevated content'),
      '#open' => TRUE,
    ];
    $build['include_details']['include'] = $this->buildLinksToContentUl($entity->getIncluded());

    $build['exclude_details'] = [
      '#type' => 'details',
      '#title' => $this->t('Excluded content'),
      '#open' => TRUE,
    ];
    $build['exclude_details']['exclude'] = $this->buildLinksToContentUl($entity->getExcluded());

    return $build;
  }

  /**
   * Generates the links to the content formatted as <ul>.
   *
   * @param mixed $elevates
   *   The titles and ids of the nodes.
   * @param mixed $label
   *   The label of the UL.
   *
   * @return array
   *   Render array with titles as links, wrapped in an <ul>.
   */
  private function buildLinksToContentUl($elevates, $label = NULL) {
    $links = [];
    foreach ($elevates as $title) {
      $id = _samhsa_term_elevation_extract_id($title);
      if ($p = strpos($id, '-')) {
        $hash = substr($id, 0, $p);
        $site = $this->websitesBases[$hash];
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
    }
    $content = [
      '#theme' => 'item_list',
      '#title' => $label,
      '#items' => $links,
      '#list_type' => 'ul',
      '#wrapper_attributes' => ['class' => 'container'],
    ];
    return $content;
  }

  /**
   * Generates the links to the content formatted as <table>.
   *
   * @param array $elevates
   *   The titles and ids of the nodes.
   * @param mixed $label
   *   The label of the UL.
   *
   * @return array
   *   Render array with titles as links, wrapped in a <table>.
   */
  private function buildLinksToContentTable(array $elevates, $label) {
    $data = [];
    foreach ($elevates as $title) {
      $id = _samhsa_term_elevation_extract_id($title);
      if ($p = strpos($id, '-')) {
        $hash = substr($id, 0, $p);
        $site = $this->websitesBases[$hash];
        preg_match('/node\/(.*?):/', $id, $matches);
        if (substr($site, -1) == '/') {
          $string = "{$site}node/{$matches[1]}";
        }
        else {
          $string = "{$site}/node/{$matches[1]}";
        }
        $url = Url::fromUri($string);
        $data[] = [Link::fromTextAndUrl($title, $url)->toString()];
      }
    }
    $content = [
      '#theme' => 'table',
      '#header' => [$label],
      '#rows' => $data,
      '#attributes' => [
        'class' => ['sahmsa-term-elevation-scroll'],
      ],
      '#wrapper_attributes' => ['class' => 'container'],
    ];
    return $content;
  }

}
