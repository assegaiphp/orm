<?php

namespace Assegai\Orm\Exceptions;

class ClassNotFoundException extends ORMException
{
  public function __construct(string $className)
  {
    $message = sprintf("Class %s not found", $className);
    parent::__construct($message);
  }
}