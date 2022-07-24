<?php

namespace Assegai\Orm\Exceptions;

use Exception;

class ORMException extends Exception
{
  public function __construct(protected $message)
  {
    parent::__construct($message);
  }

  public function __toString(): string
  {
    parent::__toString();
    return json_encode([
      'statusCode' => 500,
      'message' => sprintf("ORM Exception: %s", $this->message),
      'error' => 'Internal Server Error'
    ]);
  }
}