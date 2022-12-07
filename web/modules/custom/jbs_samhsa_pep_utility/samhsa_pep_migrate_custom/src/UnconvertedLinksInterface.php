<?php

namespace Drupal\samhsa_pep_migrate_custom;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Unconverted links entities.
 *
 * @ingroup samhsa_pep_migrate_custom
 */
interface UnconvertedLinksInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Unconverted links name.
   *
   * @return string
   *   Name of the Unconverted links.
   */
  public function getName();

  /**
   * Sets the Unconverted links name.
   *
   * @param string $name
   *   The Unconverted links name.
   *
   * @return \Drupal\samhsa_pep_migrate_custom\UnconvertedLinksInterface
   *   The called Unconverted links entity.
   */
  public function setName($name);

  /**
   * Gets the Unconverted links creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Unconverted links.
   */
  public function getCreatedTime();

  /**
   * Sets the Unconverted links creation timestamp.
   *
   * @param int $timestamp
   *   The Unconverted links creation timestamp.
   *
   * @return \Drupal\samhsa_pep_migrate_custom\UnconvertedLinksInterface
   *   The called Unconverted links entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Unconverted links published status indicator.
   *
   * Unpublished Unconverted links are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Unconverted links is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Unconverted links.
   *
   * @param bool $published
   *   TRUE to set this Unconverted links to published, FALSE to set it to
   *   unpublished.
   *
   * @return \Drupal\samhsa_pep_migrate_custom\UnconvertedLinksInterface
   *   The called Unconverted links entity.
   */
  public function setPublished($published);

}
