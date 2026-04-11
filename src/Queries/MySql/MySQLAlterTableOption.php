<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Queries\Sql\SQLAlterTableOption;
use Assegai\Orm\Queries\Sql\SQLColumnDefinition;

/**
 * MySQL-specific ALTER TABLE builder.
 */
class MySQLAlterTableOption extends SQLAlterTableOption
{
  /**
   * Add a column with optional MySQL-only positioning hints.
   *
   * @param SQLColumnDefinition $dataType The column definition to add.
   * @param bool|null $first Whether the column should be added as the first column.
   * @param string|null $afterColumn The column after which the new column should be placed.
   * @return static Returns the current alter-table builder for fluent chaining.
   */
  public function addColumn(SQLColumnDefinition $dataType, ?bool $first = false, ?string $afterColumn = null): static
  {
    parent::addColumn($dataType);

    if ($first) {
      $this->query->appendQueryString(tail: 'FIRST');
    } elseif (!is_null($afterColumn)) {
      $this->query->appendQueryString(tail: 'AFTER ' . $this->query->quoteIdentifier($afterColumn));
    }

    return $this;
  }

  /**
   * Modify an existing column definition using MySQL syntax.
   *
   * @param SQLColumnDefinition $dataType The replacement column definition.
   * @return static Returns the current alter-table builder for fluent chaining.
   */
  public function modifyColumn(SQLColumnDefinition $dataType): static
  {
    $this->query->appendQueryString(tail: 'MODIFY COLUMN ' . $dataType);

    return $this;
  }
}