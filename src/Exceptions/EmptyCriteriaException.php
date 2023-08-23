<?php

namespace Assegai\Orm\Exceptions;

class EmptyCriteriaException extends ValidationException
{
  public function __construct($methodName)
  {
    parent::__construct(sprintf("Empty criteria(s) are not allowed for the %s method", $methodName));
  }
}