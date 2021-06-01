<?php

namespace Drupal\samhsa_term_elevation\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the SAHMSA Term Elevation entity.
 *
 * @ingroup samhsa_term_elevation
 *
 * @ContentEntityType(
 *   id = "samhsa_term_elevation",
 *   label = @Translation("SAHMSA Term Elevation Queries"),
 *   handlers = {
 *     "view_builder" = "Drupal\samhsa_term_elevation\Controller\DefaultViewerController",
 *     "list_builder" = "Drupal\samhsa_term_elevation\SamhsaTermElevationListBuilder",
 *     "views_data" = "Drupal\samhsa_term_elevation\Entity\SamhsaTermElevationViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\samhsa_term_elevation\Form\SamhsaTermElevationForm",
 *       "add" = "Drupal\samhsa_term_elevation\Form\SamhsaTermElevationForm",
 *       "edit" = "Drupal\samhsa_term_elevation\Form\SamhsaTermElevationForm",
 *       "delete" = "Drupal\samhsa_term_elevation\Form\SamhsaTermElevationDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\samhsa_term_elevation\SamhsaTermElevationHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\samhsa_term_elevation\SamhsaTermElevationAccessControlHandler",
 *   },
 *   base_table = "samhsa_term_elevation",
 *   translatable = FALSE,
 *   admin_permission = "administer sahmsa term elevation entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "query",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/samhsa_term_elevation/{samhsa_term_elevation}",
 *     "add-form" = "/admin/structure/samhsa_term_elevation/add",
 *     "edit-form" = "/admin/structure/samhsa_term_elevation/{samhsa_term_elevation}/edit",
 *     "delete-form" = "/admin/structure/samhsa_term_elevation/{samhsa_term_elevation}/delete",
 *     "collection" = "/admin/structure/samhsa_term_elevation",
 *   },
 *   field_ui_base_route = "samhsa_term_elevation.settings"
 * )
 */
class SamhsaTermElevation extends ContentEntityBase {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public function getQuery() {
    return $this->get('query')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setQuery($query) {
    $this->set('query', $query);
    return $this;
  }
  /**
   * {@inheritdoc}
   */
  public function getIncluded() {
    $value = NULL;
    foreach ($this->get('elnid')->getValue() as $item) {
      $value[] = $item['value'];
    }
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function getExcluded() {
    $value = NULL;
    foreach ($this->get('exnid')->getValue() as $item) {
      $value[] = $item['value'];
    }
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getElevatedIds() {
    return $this->iterateValues($this->get('elnid')->getValue(), 'value');
  }

  /**
   * {@inheritdoc}
   */
  public function getExcludedIds() {
    return $this->iterateValues($this->get('exnid')->getValue(), 'value');
  }

  /**
   * {@inheritdoc}
   */
  protected function iterateValues($array_in, $return = 'entity') {
    $array_out = [];
    if ($array_in && is_array($array_in)) {
      foreach ($array_in as $value) {
        $array_out[] = $value[$return];
      }
    }
    return $array_out;
  }


  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['query'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Query'))
      ->setDescription(t('The search term or phrase whose results will be changed.'))
      ->setSettings([
        'max_length' => 100,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->addConstraint('UniqueField', [
        'message' => 'An override for %value already exists.',
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['elnid'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Promoted Nodes'))
      ->setDescription(t('Nodes that should appear at the top of the results.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['exnid'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Excluded Nodes'))
      ->setDescription(t('Nodes that should NOT appear in the results.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);


    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    return $fields;
  }

}
