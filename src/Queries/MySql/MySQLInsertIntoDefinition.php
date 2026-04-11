<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Queries\Sql\SQLInsertIntoDefinition;

class MySQLInsertIntoDefinition extends SQLInsertIntoDefinition
{
  public function singleRow(array $columns = []): MySQLInsertIntoStatement
  {
    return new MySQLInsertIntoStatement(
      query: $this->query,
      columns: $columns
    );
  }

  public function multipleRows(array $columns = []): MySQLInsertIntoMultipleStatement
  {
    return new MySQLInsertIntoMultipleStatement(
      query: $this->query,
      columns: $columns
    );
  }
}