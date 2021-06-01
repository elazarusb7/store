<?php

namespace Drupal\samhsa_pep_utility\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Drupal\Component\Render\FormattableMarkup;
/**
 * Validates the phone number constraint.
 */
class PhoneNumberConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
      if(is_array($value->getValue()) && isset($value->getValue()[0])) {
          $phone_number = $value->getValue()[0]['value'];
          $max = '10';
          if (\Drupal::currentUser()->hasPermission('create internal order')) {
              $max = '15';
          }
          if ($phone_number === NULL || $phone_number === '') {
              $this->context->buildViolation($constraint->requiredMessage)
                  ->addViolation();
              return;
          }

          if (isset($phone_number) && !is_numeric($phone_number)) {
              $this->context->buildViolation($constraint->invalidMessage)
                  ->setParameter('%value', $this->formatValue($phone_number))
                  ->addViolation();
              return;
          }

          if (isset($phone_number) && is_numeric($phone_number) &&
              (strlen($phone_number) < 10 || strlen($phone_number) > $max)) {
              $str = $max == 15 ? '10 - 15' : '10';
              $this->context->buildViolation($constraint->lengthMessage)
                  ->setParameter('%value', $this->formatValue($phone_number))
                  ->setParameter('%str', $str)
                  ->addViolation();
              return;
          }

          if (!empty(\Drupal::hasService('samhsa_pep_utility.pep_utility_functions')) && isset($phone_number)) {
              // Check phone for all 0.
              if(\Drupal::service('samhsa_pep_utility.pep_utility_functions')->allCharactersSameAsChar($phone_number, '0')){
                  $this->context->addViolation($constraint->allZeros, ['%value' => $phone_number]);
              }

              // Only check international phone numbers for the leading 0.
              if(strlen($phone_number) == 10) {
                  // Check phone number leading character. (area code)
                  if (\Drupal::service('samhsa_pep_utility.pep_utility_functions')->hasLeadingChar($phone_number, '0', 0)) {
                      $highlighted_phone = \Drupal::service('samhsa_pep_utility.pep_utility_functions')->highlightChar($phone_number, 0);
                      $this->context->addViolation($constraint->startWithZero, ['%value' => $highlighted_phone]);
                  }

                  // Check phone number 4th character. (number)
                  if (\Drupal::service('samhsa_pep_utility.pep_utility_functions')->hasLeadingChar($phone_number, '0', 3)) {
                      $highlighted_phone = \Drupal::service('samhsa_pep_utility.pep_utility_functions')->highlightChar($phone_number, 3);
                      $this->context->addViolation($constraint->prefixStartWithZero, ['%value' => $highlighted_phone]);
                  }
              }
          }
      }
  }

}
