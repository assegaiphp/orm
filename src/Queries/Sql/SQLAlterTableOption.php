<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Traits\ExecutableTrait;

/**
 * Base fluent builder for ALTER TABLE operations shared across SQL dialects.
 */
class SQLAlterTableOption
{
  use ExecutableTrait;

  /**
   * Create a new ALTER TABLE option builder.
   *
   * @param SQLQuery $query The query instance being built.
   */
  public function __construct(protected readonly SQLQuery $query)
  {
  }

  /**
   * Add a column to the target table.
   *
   * @param SQLColumnDefinition $dataType The column definition to add.
   * @return static Returns the current alter-table builder for fluent chaining.
   */
  public function addColumn(SQLColumnDefinition $dataType): static
  {
    $this->query->appendQueryString(tail: 'ADD COLUMN ' . $dataType);

    return $this;
  }

  /**
   * Rename an existing column.
   *
   * @param string $oldColumnName The current column name.
   * @param string $newColumnName The new column name.
   * @return static Returns the current alter-table builder for fluent chaining.
   */
  public function renameColumn(string $oldColumnName, string $newColumnName): static
  {
    $this->query->appendQueryString(
      tail: 'RENAME COLUMN ' . $this->query->quoteIdentifier($oldColumnName) . ' TO ' . $this->query->quoteIdentifier($newColumnName)
    );

    return $this;
  }

  /**
   * Drop a column from the target table.
   *
   * @param string $columnName The column to drop.
   * @return static Returns the current alter-table builder for fluent chaining.
   */
  public function dropColumn(string $columnName): static
  {
    $this->query->appendQueryString(tail: 'DROP COLUMN ' . $this->query->quoteIdentifier($columnName));

    return $this;
  }
}