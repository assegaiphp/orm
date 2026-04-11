<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Queries\Sql\SQLInsertIntoDefinition;

class SQLiteInsertIntoDefinition extends SQLInsertIntoDefinition
{
  public function singleRow(array $columns = []): SQLiteInsertIntoStatement
  {
    return new SQLiteInsertIntoStatement(
      query: $this->query,
      columns: $columns
    );
  }

  public function multipleRows(array $columns = []): SQLiteInsertIntoMultipleStatement
  {
    return new SQLiteInsertIntoMultipleStatement(
      query: $this->query,
      columns: $columns
    );
  }
}