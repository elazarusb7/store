<?php

namespace Drupal\samhsa_pep_migrate_custom\Entity;

use Drupal;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\samhsa_pep_migrate_custom\UnconvertedLinksInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Unconverted links entity.
 *
 * @ingroup samhsa_pep_migrate_custom
 *
 * @ContentEntityType(
 *   id = "unconverted_links",
 *   label = @Translation("Unconverted links"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" =
 *   "Drupal\samhsa_pep_migrate_custom\UnconvertedLinksListBuilder",
 *     "views_data" =
 *   "Drupal\samhsa_pep_migrate_custom\Entity\UnconvertedLinksViewsData",
 *
 *     "form" = {
 *       "default" =
 *   "Drupal\samhsa_pep_migrate_custom\Form\UnconvertedLinksForm",
 *       "add" = "Drupal\samhsa_pep_migrate_custom\Form\UnconvertedLinksForm",
 *       "edit" = "Drupal\samhsa_pep_migrate_custom\Form\UnconvertedLinksForm",
 *       "delete" =
 *   "Drupal\samhsa_pep_migrate_custom\Form\UnconvertedLinksDeleteForm",
 *     },
 *     "access" =
 *   "Drupal\samhsa_pep_migrate_custom\UnconvertedLinksAccessControlHandler",
 *     "route_provider" = {
 *       "html" =
 *   "Drupal\samhsa_pep_migrate_custom\UnconvertedLinksHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "unconverted_links",
 *   admin_permission = "administer unconverted links entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "url",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/unconverted_links/{unconverted_links}",
 *     "add-form" = "/admin/structure/unconverted_links/add",
 *     "edit-form" =
 *   "/admin/structure/unconverted_links/{unconverted_links}/edit",
 *     "delete-form" =
 *   "/admin/structure/unconverted_links/{unconverted_links}/delete",
 *     "collection" = "/admin/structure/unconverted_links",
 *   },
 *   field_ui_base_route = "unconverted_links.settings"
 * )
 */
class UnconvertedLinks extends ContentEntityBase implements UnconvertedLinksInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
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
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? NodeInterface::PUBLISHED : NodeInterface::NOT_PUBLISHED);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Unconverted links entity.'))
      ->setSettings([
        'max_length' => 50,
      ])
      ->setReadOnly(TRUE);

    $fields['url'] = BaseFieldDefinition::create('string')
      ->setLabel(t('URL'))
      ->setDescription(t('The link URL.'))
      ->setSettings([
        'max_length' => 510,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['bundle'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Bundle'))
      ->setDescription(t('The content type.'))
      ->setSettings([
        'max_length' => 250,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['nid'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('NID'))
      ->setDescription(t('The node ID.'));

    return $fields;
  }

}
