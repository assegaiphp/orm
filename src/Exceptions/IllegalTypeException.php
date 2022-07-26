<?php

namespace Assegai\Orm\Exceptions;

class IllegalTypeException extends ORMException
{
  public function __construct(string $expected, string $actual)
  {
    $message = sprintf("Illegal type %s. Expected %s", $actual, $expected);
    parent::__construct($message);
  }
}