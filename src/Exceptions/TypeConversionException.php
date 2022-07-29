<?php

namespace Assegai\Orm\Exceptions;

class TypeConversionException extends ORMException
{
  public function __construct(string $message)
  {
    parent::__construct(sprintf("Type Conversion Error: %s", $message));
  }
}