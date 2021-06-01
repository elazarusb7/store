<?php

namespace Drupal\samhsa_pep_utility\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the PhoneExtension constraint.
 */
class PhoneExtensionConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    foreach ($items as $item) {
      // Check if ext is all integers.
      if (!preg_match('/^[0-9]*$/', $item->value)) {
        $this->context->addViolation($constraint->notInteger, ['%value' => $item->value]);
      }

      // Check if ext no longer than 6 digits.
      if (strlen($item->value) > 6) {
        $this->context->addViolation($constraint->tooLong, ['%value' => $item->value]);
      }

      // Check ext for all 0.
      if (!empty(\Drupal::hasService('samhsa_pep_utility.pep_utility_functions'))) {
        if(\Drupal::service('samhsa_pep_utility.pep_utility_functions')->allCharactersSameAsChar($item->value, '0')){
          $this->context->addViolation($constraint->allZeros, ['%value' => $item->value]);
        }

        // Check ext leading character.
        if(\Drupal::service('samhsa_pep_utility.pep_utility_functions')->hasLeadingChar($item->value, '0')){
          $this->context->addViolation($constraint->startWithZero, ['%value' => $item->value]);
        }
      }
    }
  }
}
