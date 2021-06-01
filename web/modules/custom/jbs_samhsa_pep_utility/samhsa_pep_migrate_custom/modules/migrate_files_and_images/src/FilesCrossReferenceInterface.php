<?php

/**
 * @file
 * Contains \Drupal\migrate_files_and_images\FilesCrossReferenceInterface.
 */

namespace Drupal\migrate_files_and_images;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Files cross reference entities.
 *
 * @ingroup migrate_files_and_images
 */
interface FilesCrossReferenceInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {
  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Files cross reference name.
   *
   * @return string
   *   Name of the Files cross reference.
   */
  public function getName();

  /**
   * Sets the Files cross reference name.
   *
   * @param string $name
   *   The Files cross reference name.
   *
   * @return \Drupal\migrate_files_and_images\FilesCrossReferenceInterface
   *   The called Files cross reference entity.
   */
  public function setName($name);

  /**
   * Gets the Files cross reference creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Files cross reference.
   */
  public function getCreatedTime();

  /**
   * Sets the Files cross reference creation timestamp.
   *
   * @param int $timestamp
   *   The Files cross reference creation timestamp.
   *
   * @return \Drupal\migrate_files_and_images\FilesCrossReferenceInterface
   *   The called Files cross reference entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Files cross reference published status indicator.
   *
   * Unpublished Files cross reference are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Files cross reference is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Files cross reference.
   *
   * @param bool $published
   *   TRUE to set this Files cross reference to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\migrate_files_and_images\FilesCrossReferenceInterface
   *   The called Files cross reference entity.
   */
  public function setPublished($published);

}
