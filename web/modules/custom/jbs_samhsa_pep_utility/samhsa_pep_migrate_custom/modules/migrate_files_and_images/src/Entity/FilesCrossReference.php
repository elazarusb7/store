<?php

namespace Drupal\migrate_files_and_images\Entity;

use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\migrate_files_and_images\FilesCrossReferenceInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Files cross reference entity.
 *
 * @ingroup migrate_files_and_images
 *
 * @ContentEntityType(
 *   id = "files_cross_reference",
 *   label = @Translation("Files cross reference"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\migrate_files_and_images\FilesCrossReferenceListBuilder",
 *     "views_data" = "Drupal\migrate_files_and_images\Entity\FilesCrossReferenceViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\migrate_files_and_images\Form\FilesCrossReferenceForm",
 *       "add" = "Drupal\migrate_files_and_images\Form\FilesCrossReferenceForm",
 *       "edit" = "Drupal\migrate_files_and_images\Form\FilesCrossReferenceForm",
 *       "delete" = "Drupal\migrate_files_and_images\Form\FilesCrossReferenceDeleteForm",
 *     },
 *     "access" = "Drupal\migrate_files_and_images\FilesCrossReferenceAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\migrate_files_and_images\FilesCrossReferenceHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "files_cross_reference",
 *   admin_permission = "administer files cross reference entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/files_cross_reference/{files_cross_reference}",
 *     "add-form" = "/admin/structure/files_cross_reference/add",
 *     "edit-form" = "/admin/structure/files_cross_reference/{files_cross_reference}/edit",
 *     "delete-form" = "/admin/structure/files_cross_reference/{files_cross_reference}/delete",
 *     "collection" = "/admin/structure/files_cross_reference",
 *   },
 *   field_ui_base_route = "files_cross_reference.settings"
 * )
 *
 * MySQL creation command:
 * CREATE TABLE `files_cross_reference` (
 * `id` int(11) NOT NULL AUTO_INCREMENT,
 * `name` varchar(256) DEFAULT NULL,
 * `d8fid` int(11) DEFAULT NULL,
 * PRIMARY KEY (`id`)
 * ) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COMMENT='The base table for files_cross_reference entities.';
 */
class FilesCrossReference extends ContentEntityBase implements FilesCrossReferenceInterface {
  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
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
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Files cross reference entity.'))
      ->setReadOnly(TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Files cross reference entity.'))
      ->setSettings([
        'max_length' => 256,
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

    $fields['d8fid'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('D8 File ID'))
      ->setDescription(t('The ID of the file on D8 website.'));

    return $fields;
  }

  /**
   * Search for a record that has a given file name.
   *
   * @param string $name
   *   The file name.
   *
   * @return objectbool
   *   The found record or false.
   */
  public static function loadByName($name) {
    $query = \Drupal::database()->select('files_cross_reference', 'f');
    $query->fields('f');
    $query->condition('f.name', $name);
    if ($result = $query->execute()->fetchAll()) {
      $result = $result[0];
    }
    else {
      $result = FALSE;
    }
    return $result;
  }

}
