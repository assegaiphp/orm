<?php

namespace Assegai\Orm\Exceptions;

class SaveException extends ORMException
{
  public function __construct(string $details)
  {
    parent::__construct(sprintf("A save error occurred: %s", $details));
  }
}