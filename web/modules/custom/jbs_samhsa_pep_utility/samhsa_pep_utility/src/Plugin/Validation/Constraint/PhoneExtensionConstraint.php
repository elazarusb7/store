<?php

namespace Drupal\samhsa_pep_utility\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted value is numeric and no more than 6 digits.
 *
 * @Constraint(
 *   id = "PhoneExtensionConstraint",
 *   label = @Translation("Phone Extension", context = "Validation"),
 *   type = "string"
 * )
 */
class PhoneExtensionConstraint extends Constraint {

  /**
   * The message that will be shown if the value is not an integer.
   */
  public $notInteger = 'Extension %value contains non-numeric characters.  Only digits are allowed';

  /**
   * The message that will be shown if the value is more than 6 digits.
   */
  public $tooLong = 'Extension %value cannot be longer than 6 digits';

  /**
   * The message that will be shown if the value has all 0 digits.
   */
  public $allZeros = 'Extension %value cannot contain all zeros';

  /**
   * The message that will be shown if the value started with 0.
   */
  public $startWithZero = 'Extension %value cannot start with zero';

}
