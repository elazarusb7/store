<?php

namespace Drupal\samhsa_pep_order_states_workflow;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Interface for services that provide workflow related helper methods.
 */
interface WorkflowHelperInterface {

  /**
   * Returns the available transition states of an entity for the given user.
   *
   * If no user is passed, the logged in user is checked. If no user is logged
   * in, an anonymous account is passed.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity with the states.
   * @param \Drupal\Core\Session\AccountInterface|null $user
   *   The account interface object. Can be left empty.
   *
   * @return array
   *   An array of transition state labels.
   */
  public function getAvailableStates(FieldableEntityInterface $entity, AccountInterface $user = NULL);

  /**
   * Returns the available transitions of an entity for the given user.
   *
   * If no user is passed, the logged in user is checked. If no user is logged
   * in, an anonymous account is passed.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity with the states.
   * @param \Drupal\Core\Session\AccountInterface|null $user
   *   The account interface object. Can be left empty.
   *
   * @return array
   *   An array of transition labels.
   */
  public function getAvailableTransitions(FieldableEntityInterface $entity, AccountInterface $user);

  /**
   * Returns the state field definitions of an entity.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity that has the state field.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface[]
   *   Returns an array of state field definitions.
   */
  public static function getEntityStateFieldDefinitions(FieldableEntityInterface $entity);

  /**
   * Returns the state field definition of an entity.
   *
   * In the current project every entity with a state has only one state field
   * so this method returns the first available field definitions of the
   * entity's field definitions otherwise it returns NULL.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity that has the state field.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface|null
   *   Returns the state field definition of the entity or NULL if none is
   *   found.
   */
  public static function getEntityStateFieldDefinition(FieldableEntityInterface $entity);

  /**
   * Returns the StateItem field for a given entity.
   *
   * In the current project every entity with a state has only one state field
   * so this method returns the first available field definitions of the
   * entity's field definitions.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity for which to return the state field.
   *
   * @return \Drupal\state_machine\Plugin\Field\FieldType\StateItemInterface
   *   The state field.
   *
   * @throws \Exception
   *   Thrown when the entity does not have a state field.
   */
  public function getEntityStateField(FieldableEntityInterface $entity);

  /**
   * Returns whether the entity has a state field and supports workflow.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity to check.
   *
   * @return bool
   *   TRUE if the entity has a state field. FALSE otherwise.
   */
  public function hasEntityStateField(FieldableEntityInterface $entity);

  /**
   * Returns all states for the specific workflow id.
   *
   * @param string $id
   *   The id to get workflow yml file details for.
   *
   * @return string array of all possible order states
   *   array string.
   */
  public function getAllStates(string $id);

}
