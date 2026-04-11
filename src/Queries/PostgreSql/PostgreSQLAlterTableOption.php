<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Queries\Sql\SQLAlterTableOption;
use Assegai\Orm\Queries\Sql\SQLColumnDefinition;

/**
 * PostgreSQL-specific ALTER TABLE builder.
 */
class PostgreSQLAlterTableOption extends SQLAlterTableOption
{
  /**
   * Change the type of an existing column using PostgreSQL syntax.
   *
   * @param string|SQLColumnDefinition $column The target column name or definition.
   * @param string|null $typeExpression The target type expression when a raw column name is supplied.
   * @param string|null $using The optional USING expression applied during the type conversion.
   * @return static Returns the current alter-table builder for fluent chaining.
   */
  public function alterColumnType(string|SQLColumnDefinition $column, ?string $typeExpression = null, ?string $using = null): static
  {
    if ($column instanceof SQLColumnDefinition) {
      $typeExpression = $column->getTypeExpression();
      $column = $column->name;
    }

    $queryString = 'ALTER COLUMN ' . $this->query->quoteIdentifier($column) . ' TYPE ' . $typeExpression;

    if (!is_null($using) && $using !== '') {
      $queryString .= ' USING ' . $using;
    }

    $this->query->appendQueryString(tail: $queryString);

    return $this;
  }

  /**
   * Set a default value on an existing column using PostgreSQL syntax.
   *
   * @param string $columnName The column whose default should be updated.
   * @param mixed $value The default value expression to apply.
   * @return static Returns the current alter-table builder for fluent chaining.
   */
  public function setDefault(string $columnName, mixed $value): static
  {
    $default = (new SQLColumnDefinition(name: $columnName, defaultValue: $value, dialect: $this->query->getDialect()))
      ->getDefaultExpression();

    if (!is_null($default)) {
      $this->query->appendQueryString(
        tail: 'ALTER COLUMN ' . $this->query->quoteIdentifier($columnName) . ' SET DEFAULT ' . $default
      );
    }

    return $this;
  }
}