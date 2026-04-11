<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Queries\Sql\SQLQuery;
use Assegai\Orm\Queries\Sql\SQLQueryType;

class SQLiteQuery extends SQLQuery
{
  public function insertInto(string $tableName): SQLiteInsertIntoDefinition
  {
    $this->init();
    $this->setQueryType(SQLQueryType::INSERT);

    return new SQLiteInsertIntoDefinition(query: $this, tableName: $tableName);
  }
}