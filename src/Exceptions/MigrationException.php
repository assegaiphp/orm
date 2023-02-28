<?php

namespace Assegai\Orm\Exceptions;

/**
 * MigrationException is a class that extends the ORMException and is used to
 * throw exceptions related to migrations.
 */
class MigrationException extends ORMException
{
  /**
   * Constructor method to instantiate a new MigrationException object.
   *
   * @param string $message The error message to be included with the exception.
   */
  public function __construct(string $message)
  {
    parent::__construct("Migration error: $message");
  }
}
