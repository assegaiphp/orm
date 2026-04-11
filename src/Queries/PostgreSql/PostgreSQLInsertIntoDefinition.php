<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Queries\Sql\SQLInsertIntoDefinition;

class PostgreSQLInsertIntoDefinition extends SQLInsertIntoDefinition
{
  public function singleRow(array $columns = []): PostgreSQLInsertIntoStatement
  {
    return new PostgreSQLInsertIntoStatement(
      query: $this->query,
      columns: $columns
    );
  }

  public function multipleRows(array $columns = []): PostgreSQLInsertIntoMultipleStatement
  {
    return new PostgreSQLInsertIntoMultipleStatement(
      query: $this->query,
      columns: $columns
    );
  }
}