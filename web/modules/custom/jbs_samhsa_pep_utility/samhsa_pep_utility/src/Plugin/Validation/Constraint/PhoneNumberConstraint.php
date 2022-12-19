<?php

namespace Drupal\samhsa_pep_utility\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Phone Number constraint.
 *
 * @Constraint(
 *   id = "PhoneNumberConstraint",
 *   label = @Translation("Phone Number Constraint", context = "Validation"),
 * )
 */
class PhoneNumberConstraint extends Constraint {
  public $max = NULL;
  public $requiredMessage = 'The phone number is required.';
  public $invalidMessage = 'The phone number %value is not valid. Please enter 10 digits only.';
  public $notAvailableMessage = 'The phone number %value is not available.';
  public $lengthMessage = 'The phone number %value is not valid. Please enter %str digits only.';

  /**
   * The message that will be shown if the value has all 0 digits.
   */
  public $allZeros = 'Phone number %value cannot contain all zeros';

  /**
   * The message that will be shown if the value started with 0.
   */
  public $startWithZero = 'Phone number area code %value cannot start with zero';

  /**
   * The message that will be shown if the 4th character of the phone field is 0.
   */
  public $prefixStartWithZero = 'Phone number prefix %value cannot start with zero';

}
