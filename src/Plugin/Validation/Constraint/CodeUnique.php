<?php

namespace Drupal\commerce_recruiting\Plugin\Validation\Constraint;

use Drupal\Core\Validation\Plugin\Validation\Constraint\UniqueFieldConstraint;

/**
 * Checks if a code is unique.
 *
 * @Constraint(
 *   id = "CodeUnique",
 *   label = @Translation("Code unique", context = "Validation"),
 * )
 */
class CodeUnique extends UniqueFieldConstraint {

  public $message = 'The code %value is already taken.';

}
