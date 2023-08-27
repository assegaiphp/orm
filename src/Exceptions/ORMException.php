<?php

namespace Assegai\Orm\Exceptions;

use Exception;
use JsonSerializable;

class ORMException extends Exception implements JsonSerializable
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

  public function jsonSerialize(): mixed
  {
    return [
      'statusCode' => $this->status,
      'message' => sprintf("ORM Exception: %s", $this->message),
      'error' => $this->error
    ];
  }
}