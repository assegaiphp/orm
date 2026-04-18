<?php

namespace Assegai\Orm\Queries\MsSql;

use Assegai\Orm\Queries\Sql\SQLDescribeStatement;
use Assegai\Orm\Util\SqlIdentifier;

/**
 * MSSQL-specific describe statement builder.
 */
class MsSqlDescribeStatement extends SQLDescribeStatement
{
  /**
   * Build the SQL Server metadata query used for table description.
   *
   * @return string Returns the rendered SQL Server describe query.
   */
  protected function buildQueryString(): string
  {
    $dialect = $this->query->getDialect();
    $columnName = SqlIdentifier::quote('COLUMN_NAME', $dialect);
    $dataType = SqlIdentifier::quote('DATA_TYPE', $dialect);
    $isNullable = SqlIdentifier::quote('IS_NULLABLE', $dialect);
    $columnDefault = SqlIdentifier::quote('COLUMN_DEFAULT', $dialect);
    $tableName = SqlIdentifier::quote('TABLE_NAME', $dialect);
    $ordinalPosition = SqlIdentifier::quote('ORDINAL_POSITION', $dialect);
    $columnsTable = SqlIdentifier::quote('INFORMATION_SCHEMA.COLUMNS', $dialect);
    $placeholder = $this->query->addParam($this->subject);

    return "SELECT $columnName, $dataType, $isNullable, $columnDefault " .
      "FROM $columnsTable " .
      "WHERE $tableName = $placeholder " .
      "ORDER BY $ordinalPosition ASC";
  }
}
