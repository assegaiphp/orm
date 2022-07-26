<?php

namespace Assegai\Orm\Exceptions;

class NotFoundException extends ORMException
{
  public function __construct(string $tokenId)
  {
    parent::__construct(sprintf("Entry not found %s", $tokenId));
  }
}