<?php

namespace Assegai\Orm\Queries\Sql;

/**
 * Describes the minimum CREATE capability shared across SQL-family builders.
 */
interface SQLCreateDefinitionInterface
{
  /**
   * Begin a CREATE TABLE statement.
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
  ): SQLCreateTableStatement;

  /**
   * Execute the assembled CREATE statement.
   *
   * @return SQLQueryResult Returns the underlying SQL execution result.
   * @throws \Assegai\Orm\Exceptions\ORMException Thrown when query execution fails.
   */
  public function execute(): SQLQueryResult;
}
