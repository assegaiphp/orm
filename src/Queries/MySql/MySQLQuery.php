<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Queries\Sql\SQLQuery;
use Assegai\Orm\Queries\Sql\SQLQueryType;

class MySQLQuery extends SQLQuery
{
  public function insertInto(string $tableName): MySQLInsertIntoDefinition
  {
    $this->init();
    $this->setQueryType(SQLQueryType::INSERT);

    return new MySQLInsertIntoDefinition(query: $this, tableName: $tableName);
  }
}