<?php

namespace Assegai\Orm\Exceptions;

class EmptyCriteriaException extends ORMException
{
  public function __construct($methodName)
  {
    parent::__construct(sprintf("Empty criteria(s) are not allowed for the %s method", $methodName));
  }
}