<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Queries\Sql\ColumnType;
use Assegai\Orm\Queries\Sql\SQLColumnDefinition;

/**
 * SQLite-specific column-definition builder.
 */
class SQLiteColumnDefinition extends SQLColumnDefinition
{
  /**
   * Builds the SQLite-flavored column definition.
   *
   * @return string Returns the rendered SQLite column definition.
   */
  protected function buildDefinition(): string
  {
    return $this->buildSqliteDefinition();
  }

  /**
   * Resolves the SQLite-specific type expression.
   *
   * @return string Returns the SQLite type expression.
   */
  protected function resolveTypeExpression(): string
  {
    return $this->getSqliteType();
  }

  /**
   * Create a SQLite column-definition builder.
   *
   * @param string $name The column name.
   * @param ColumnType $type The column type.
   * @param string|int|array|null $lengthOrValues The length, precision, or enum values.
   * @param mixed|null $defaultValue The default value expression.
   * @param bool $nullable Whether the column allows NULL.
   * @param bool $autoIncrement Whether the column auto-increments.
   * @param string $onUpdate Optional ON UPDATE expression.
   * @param bool $isUnique Whether the column is unique.
   * @param string $uniqueKey Optional unique-key name.
   * @param bool $isPrimaryKey Whether the column is a primary key.
   * @param string $comment Optional column comment.
   */
  public function __construct(
    string $name,
    ColumnType $type = ColumnType::INT,
    null|string|int|array $lengthOrValues = null,
    mixed $defaultValue = null,
    bool $nullable = true,
    bool $autoIncrement = false,
    string $onUpdate = "",
    bool $isUnique = false,
    string $uniqueKey = "",
    bool $isPrimaryKey = false,
    string $comment = "",
  )
  {
    parent::__construct(
      name: $name,
      type: $type,
      lengthOrValues: $lengthOrValues,
      defaultValue: $defaultValue,
      nullable: $nullable,
      autoIncrement: $autoIncrement,
      onUpdate: $onUpdate,
      isUnique: $isUnique,
      uniqueKey: $uniqueKey,
      isPrimaryKey: $isPrimaryKey,
      comment: $comment,
      dialect: SQLDialect::SQLITE,
    );
  }
}
