<?php

namespace Assegai\Orm\Queries\MsSql;

use Assegai\Orm\Queries\Sql\SQLAlterTableOption;
use Assegai\Orm\Queries\Sql\SQLColumnDefinition;

/**
 * MSSQL-specific ALTER TABLE builder.
 */
class MsSqlAlterTableOption extends SQLAlterTableOption
{
  /**
   * Change the type of an existing column using SQL Server syntax.
   *
   * @param string|SQLColumnDefinition $column The target column name or definition.
   * @param string|null $typeExpression The target type expression when a raw column name is supplied.
   * @return static Returns the current alter-table builder for fluent chaining.
   */
  public function alterColumnType(string|SQLColumnDefinition $column, ?string $typeExpression = null): static
  {
    if ($column instanceof SQLColumnDefinition) {
      $typeExpression = $column->getTypeExpression();
      $column = $column->name;
    }

    $this->query->appendQueryString(
      tail: 'ALTER COLUMN ' . $this->query->quoteIdentifier($column) . ' ' . $typeExpression
    );

    return $this;
  }
}
