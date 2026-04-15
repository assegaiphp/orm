<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Queries\Sql\SQLCreateDefinitionInterface;
use Assegai\Orm\Queries\Sql\SQLCreateTableStatement;
use Assegai\Orm\Queries\Sql\SQLTableCreateDefinition;

/**
 * SQLite-specific CREATE entry point.
 */
class SQLiteCreateDefinition extends SQLTableCreateDefinition implements SQLCreateDefinitionInterface
{
  /**
   * Begins a SQLite CREATE TABLE statement.
   *
   * @param string $tableName The table name to create.
   * @param bool $isTemporary Indicates whether TEMPORARY should be emitted.
   * @param bool $checkIfNotExists Indicates whether IF NOT EXISTS should be emitted.
   * @return SQLiteCreateTableStatement Returns the SQLite CREATE TABLE statement builder.
   */
  public function table(
    string $tableName,
    bool $isTemporary = false,
    bool $checkIfNotExists = true,
  ): SQLiteCreateTableStatement
  {
    return $this->createTableStatement(
      tableName: $tableName,
      isTemporary: $isTemporary,
      checkIfNotExists: $checkIfNotExists,
    );
  }

  /**
   * Creates the SQLite CREATE TABLE statement builder.
   *
   * @param string $tableName The table name to create.
   * @param bool $isTemporary Indicates whether TEMPORARY should be emitted.
   * @param bool $checkIfNotExists Indicates whether IF NOT EXISTS should be emitted.
   * @return SQLiteCreateTableStatement Returns the SQLite CREATE TABLE statement builder.
   */
  protected function createTableStatement(
    string $tableName,
    bool $isTemporary = false,
    bool $checkIfNotExists = true,
  ): SQLCreateTableStatement
  {
    return new SQLiteCreateTableStatement(
      query: $this->query,
      tableName: $tableName,
      isTemporary: $isTemporary,
      checkIfNotExists: $checkIfNotExists,
    );
  }
}
