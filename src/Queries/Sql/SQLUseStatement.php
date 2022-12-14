<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Traits\ExecutableTrait;

final class SQLUseStatement
{
  use ExecutableTrait;

  public function __construct(
    private readonly SQLQuery $query,
    private readonly string $dbName
  )
  {
    $this->query->appendQueryString("USE $this->dbName");
  }
}