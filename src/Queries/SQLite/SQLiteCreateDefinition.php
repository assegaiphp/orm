<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Queries\Sql\SQLQuery;
use Assegai\Orm\Queries\Sql\SQLCreateDefinitionInterface;
use Assegai\Orm\Queries\Sql\SQLQueryResult;

/**
 * SQLite-specific CREATE entry point.
 */
class SQLiteCreateDefinition implements SQLCreateDefinitionInterface
{
  /**
   * Creates a SQLite CREATE definition bound to the supplied query root.
   *
   * @param SQLQuery $query Receives the rendered CREATE statement fragments.
   */
  public function __construct(private readonly SQLQuery $query)
  {
  }

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
    return new SQLiteCreateTableStatement(
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
