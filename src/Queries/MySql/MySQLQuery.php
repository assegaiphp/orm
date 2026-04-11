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

  public function update(string $tableName, bool $lowPriority = false, bool $ignore = false): MySQLUpdateDefinition
  {
    $this->init();
    $this->setQueryType(SQLQueryType::UPDATE);

    return new MySQLUpdateDefinition(
      query: $this,
      tableName: $tableName,
      lowPriority: $lowPriority,
      ignore: $ignore,
    );
  }
}