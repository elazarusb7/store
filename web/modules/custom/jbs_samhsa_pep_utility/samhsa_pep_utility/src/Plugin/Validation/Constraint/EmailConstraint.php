<?php

namespace Drupal\samhsa_pep_utility\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted value is valid email address.
 *
 * @Constraint(
 *   id = "EmailConstraint",
 *   label = @Translation("Email", context = "Validation"),
 *   type = "string"
 * )
 */
class EmailConstraint extends Constraint {

  // The message that will be shown if the email value is not a valid email address.
  public $notValidEmail = 'Email %value is not valid.';
}
