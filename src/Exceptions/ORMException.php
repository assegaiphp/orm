<?php

namespace Assegai\Orm\Exceptions;

use Exception;

class ORMException extends Exception
{
  protected int $status = 500;
  protected string $error = 'Internal Server Error';

  public function __construct(protected $message)
  {
    parent::__construct($message);
  }

  public function __toString(): string
  {
    parent::__toString();
    return json_encode([
      'statusCode' => $this->status,
      'message' => sprintf("ORM Exception: %s", $this->message),
      'error' => $this->error
    ]);
  }
}