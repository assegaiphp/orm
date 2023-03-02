<?php

namespace Assegai\Orm\Exceptions;

/**
 * IOException class extends from ORMException to represent Input/Output related errors
 * during ORM operations.
 */
class IOException extends ORMException
{
  /**
   * Constructor method to instantiate a new IOException object.
   *
   * @param string $message The error message to be included in the exception.
   */
  public function __construct(string $message)
  {
    /**
     * Call parent constructor to attach the prefix "IOException: " to the error message.
     */
    parent::__construct("IOException: " . $message);
  }
}
