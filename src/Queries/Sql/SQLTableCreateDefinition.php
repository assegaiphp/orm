<?php

namespace Assegai\Orm\Queries\Sql;

/**
 * Shared CREATE entry point for SQL dialects that support table creation.
 */
class SQLTableCreateDefinition implements SQLCreateDefinitionInterface
{
  /**
   * Creates a shared table-capable CREATE definition bound to the supplied query root.
   *
   * @param SQLQuery $query Receives the rendered CREATE statement fragments.
   */
  public function __construct(protected readonly SQLQuery $query)
  {
  }

  /**
   * Begins a CREATE TABLE statement.
   *
   * @param string $tableName The name of the table to create.
   * @param bool $isTemporary Indicates whether the table should be temporary.
   * @param bool $checkIfNotExists Indicates whether IF NOT EXISTS should be emitted.
   * @return SQLCreateTableStatement Returns the CREATE TABLE statement builder.
   */
  public function table(
    string $tableName,
    bool $isTemporary = false,
    bool $checkIfNotExists = true,
  ): SQLCreateTableStatement
  {
    return $this->createTableStatement(
      tableName: $tableName,
      isTemporary: $isTemporary,
      checkIfNotExists: $checkIfNotExists,
    );
  }

  /**
   * Creates the CREATE TABLE statement builder for the active SQL dialect.
   *
   * @param string $tableName The name of the table to create.
   * @param bool $isTemporary Indicates whether the table should be temporary.
   * @param bool $checkIfNotExists Indicates whether IF NOT EXISTS should be emitted.
   * @return SQLCreateTableStatement Returns the CREATE TABLE statement builder.
   */
  protected function createTableStatement(
    string $tableName,
    bool $isTemporary = false,
    bool $checkIfNotExists = true,
  ): SQLCreateTableStatement
  {
    return new SQLCreateTableStatement(
      query: $this->query,
      tableName: $tableName,
      isTemporary: $isTemporary,
      checkIfNotExists: $checkIfNotExists,
    );
  }

  /**
   * Executes the assembled CREATE query directly.
   *
   * @return SQLQueryResult Returns the execution result produced by the underlying query root.
   * @throws \Assegai\Orm\Exceptions\ORMException Thrown when the underlying query execution fails.
   */
  public function execute(): SQLQueryResult
  {
    return $this->query->execute();
  }
}
