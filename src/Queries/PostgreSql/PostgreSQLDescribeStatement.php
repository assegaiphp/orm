<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Queries\Sql\SQLDescribeStatement;
use Assegai\Orm\Queries\Sql\SQLQuery;
use Assegai\Orm\Util\SqlDialectHelper;

/**
 * PostgreSQL-specific describe statement builder.
 *
 * PostgreSQL does not support DESCRIBE directly, so this builder emits an
 * information_schema query instead.
 */
class PostgreSQLDescribeStatement extends SQLDescribeStatement
{
  /**
   * Create a PostgreSQL describe statement.
   *
   * @param SQLQuery $query The query instance being built.
   * @param string $subject The table or view name to describe.
   */
  public function __construct(
    protected readonly SQLQuery $query,
    protected readonly string $subject,
  ) {
    $columnName = SqlDialectHelper::quoteIdentifier('column_name', SQLDialect::POSTGRESQL);
    $dataType = SqlDialectHelper::quoteIdentifier('data_type', SQLDialect::POSTGRESQL);
    $isNullable = SqlDialectHelper::quoteIdentifier('is_nullable', SQLDialect::POSTGRESQL);
    $columnDefault = SqlDialectHelper::quoteIdentifier('column_default', SQLDialect::POSTGRESQL);
    $tableSchema = SqlDialectHelper::quoteIdentifier('table_schema', SQLDialect::POSTGRESQL);
    $tableName = SqlDialectHelper::quoteIdentifier('table_name', SQLDialect::POSTGRESQL);
    $ordinalPosition = SqlDialectHelper::quoteIdentifier('ordinal_position', SQLDialect::POSTGRESQL);
    $informationSchema = SqlDialectHelper::quoteIdentifier('information_schema', SQLDialect::POSTGRESQL);
    $columnsTable = SqlDialectHelper::quoteIdentifier('columns', SQLDialect::POSTGRESQL);
    $placeholder = $this->query->addParam($this->subject);

    $this->query->setQueryString(
      "SELECT $columnName, $dataType, $isNullable, $columnDefault " .
      "FROM $informationSchema.$columnsTable " .
      "WHERE $tableSchema = CURRENT_SCHEMA() AND $tableName = $placeholder " .
      "ORDER BY $ordinalPosition ASC"
    );
  }
}
