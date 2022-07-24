<?php

namespace Assegai\Orm\Traits;

use Assegai\Orm\Queries\Sql\SQLQueryResult;

trait ExecutableTrait
{
  public function execute(): SQLQueryResult
  {
    return $this->query->execute();
  }

  public function debug(): never
  {
    $this->query->debug();
  }
}