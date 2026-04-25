<?php

namespace Assegai\Orm\Queries\MsSql;

use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Queries\Sql\SQLAlterTableOption;
use Assegai\Orm\Queries\Sql\SQLColumnDefinition;
use Assegai\Orm\Util\SqlDialectHelper;

/**
 * MSSQL-specific ALTER TABLE builder.
 */
class MsSqlAlterTableOption extends SQLAlterTableOption
{
  public function __construct(
    \Assegai\Orm\Queries\Sql\SQLQuery $query,
    private readonly string $tableName,
  ) {
    parent::__construct($query);
  }

  public function addColumn(SQLColumnDefinition $dataType): static
  {
    $this->query->appendQueryString(tail: 'ADD ' . $dataType);

    return $this;
  }

  public function renameColumn(string $oldColumnName, string $newColumnName): static
  {
    $tableName = $this->escapeLiteral(SqlDialectHelper::quoteCompositeIdentifier($this->tableName, SQLDialect::MSSQL));
    $oldColumnName = $this->escapeLiteral($this->query->quoteIdentifier($oldColumnName));
    $newColumnName = $this->escapeLiteral(SqlDialectHelper::unqualifyIdentifier($newColumnName));

    $this->query->setQueryString(
      queryString: "EXEC sp_rename N'{$tableName}.{$oldColumnName}', N'{$newColumnName}', N'COLUMN'"
    );

    return $this;
  }

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

  private function escapeLiteral(string $value): string
  {
    return str_replace("'", "''", $value);
  }
}
