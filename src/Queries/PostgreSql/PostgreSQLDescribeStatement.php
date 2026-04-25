<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Queries\Sql\SQLDescribeStatement;
use Assegai\Orm\Util\SqlIdentifier;

/**
 * PostgreSQL-specific describe statement builder.
 *
 * PostgreSQL does not support DESCRIBE directly, so this builder emits an
 * information_schema query instead.
 */
class PostgreSQLDescribeStatement extends SQLDescribeStatement
{
  /**
   * Build the PostgreSQL metadata query used for table description.
   *
   * @return string Returns the rendered PostgreSQL describe query.
   */
  protected function buildQueryString(): string
  {
    $dialect = $this->query->getDialect();
    $columnName = SqlIdentifier::quote('column_name', $dialect);
    $dataType = SqlIdentifier::quote('data_type', $dialect);
    $isNullable = SqlIdentifier::quote('is_nullable', $dialect);
    $columnDefault = SqlIdentifier::quote('column_default', $dialect);
    $tableSchema = SqlIdentifier::quote('table_schema', $dialect);
    $tableName = SqlIdentifier::quote('table_name', $dialect);
    $ordinalPosition = SqlIdentifier::quote('ordinal_position', $dialect);
    $columnsTable = SqlIdentifier::quote('information_schema.columns', $dialect);
    $placeholder = $this->query->addParam($this->subject);

    return "SELECT $columnName, $dataType, $isNullable, $columnDefault " .
      "FROM $columnsTable " .
      "WHERE $tableSchema = CURRENT_SCHEMA() AND $tableName = $placeholder " .
      "ORDER BY $ordinalPosition ASC";
  }
}
