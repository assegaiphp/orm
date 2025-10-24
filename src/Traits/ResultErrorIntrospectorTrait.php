<?php

namespace Assegai\Orm\Traits;

use Throwable;
use Traversable;

/**
 * Trait ResultErrorIntrospectorTrait provides methods to introspect errors in result sets.
 * Classes that use this trait should have an `errors` property that is either an array or a Traversable collection of Throwable objects.
 *
 * @package Assegai\Orm\Traits
 */
trait ResultErrorIntrospectorTrait
{
  /**
   * Returns the first error from the result set. The first error is considered the earliest raised error.
   *
   * @property array|Traversable|null $errors The collection of errors.
   *
   * @return Throwable|null The first error, or null if there are no errors.
   */
  public function getFirstThrownError(): ?Throwable
  {
    if (!isset($this->errors)) {
      return null;
    }

    if ($this->errors instanceof Traversable) {
      foreach ($this->errors as $error) {
        return $error;
      }
    }

    if (!is_array($this->errors)) {
      return null;
    }

    return !empty($this->errors) ? $this->errors[0] : null;
  }

  /**
   * Returns the last thrown error from the result set. The last thrown error is considered the most recent error.
   *
   * @return Throwable|null The last thrown error, or null if there are no errors.
   */
  public function getLastThrownError(): ?Throwable
  {
    if (!isset($this->errors)) {
      return null;
    }

    $errors = $this->errors;

    if ($this->errors instanceof Traversable) {
      $errors = iterator_to_array($errors);
    }

    if (!is_array($errors)) {
      return null;
    }

    if (empty($errors)) {
      return null;
    }

    return $errors[array_key_last($errors)];
  }

}