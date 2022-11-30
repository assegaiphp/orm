<?php

namespace Assegai\Orm\Queries\QueryBuilder;

use Assegai\Orm\DataSource\DataSource;

class RelationLoader
{
  public function __construct(private readonly DataSource $connection)
  {
  }

  public function load(): mixed
  {
    // TODO: Implement load() method.
    return $this->connection;
  }
}