<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Queries\Sql\SQLQuery;
use Assegai\Orm\Queries\Sql\SQLQueryType;

class PostgreSQLQuery extends SQLQuery
{
  public function insertInto(string $tableName): PostgreSQLInsertIntoDefinition
  {
    $this->init();
    $this->setQueryType(SQLQueryType::INSERT);

    return new PostgreSQLInsertIntoDefinition(query: $this, tableName: $tableName);
  }
}