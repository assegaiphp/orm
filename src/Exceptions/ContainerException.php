<?php

namespace Assegai\Orm\Exceptions;

use Assegai\Orm\Interfaces\IStoreOwner;

class ContainerException extends ORMException
{
  public function __construct(IStoreOwner $storeOwner, string $message)
  {
    parent::__construct(sprintf("%s error: %s", $storeOwner::class, $message));
  }
}