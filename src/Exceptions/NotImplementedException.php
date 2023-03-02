<?php

namespace Assegai\Orm\Exceptions;

class NotImplementedException extends ORMException
{
  public function __construct(string $feature)
  {
    $this->code = 501;
    $this->error = 'Not Implemented';
    parent::__construct(sprintf("%s not yet implemented", $feature));
  }
}