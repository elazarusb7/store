<?php

namespace Drupal\samhsa_pep_utility\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the Email constraint.
 */
class EmailConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    foreach ($items as $item) {
      // Check if email valid.
      if (!filter_var($item->value, FILTER_VALIDATE_EMAIL)) {
        $this->context->addViolation($constraint->notValidEmail, ['%value' => $item->value]);
      }
    }
  }

}
