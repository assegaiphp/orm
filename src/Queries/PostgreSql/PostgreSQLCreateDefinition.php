<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Queries\Sql\SQLQuery;
use Assegai\Orm\Queries\Sql\SQLCreateDefinitionInterface;
use Assegai\Orm\Queries\Sql\SQLQueryResult;

/**
 * PostgreSQL-specific CREATE entry point.
 */
class PostgreSQLCreateDefinition implements SQLCreateDefinitionInterface
{
  /**
   * Creates a PostgreSQL CREATE definition bound to the supplied query root.
   *
   * @param SQLQuery $query Receives the rendered CREATE statement fragments.
   */
  public function __construct(private readonly SQLQuery $query)
  {
  }

  /**
   * Begins a PostgreSQL CREATE TABLE statement.
   *
   * @param string $tableName The table name to create.
   * @param bool $isTemporary Indicates whether TEMPORARY should be emitted.
   * @param bool $checkIfNotExists Indicates whether IF NOT EXISTS should be emitted.
   * @return PostgreSQLCreateTableStatement Returns the PostgreSQL CREATE TABLE statement builder.
   */
  public function table(
    string $tableName,
    bool $isTemporary = false,
    bool $checkIfNotExists = true,
  ): PostgreSQLCreateTableStatement
  {
    return new PostgreSQLCreateTableStatement(
      query: $this->query,
      tableName: $tableName,
      isTemporary: $isTemporary,
      checkIfNotExists: $checkIfNotExists,
    );
  }

  /**
   * Begins a PostgreSQL CREATE DATABASE statement.
   *
   * @param string $dbName The database name to create.
   * @param string $encoding The PostgreSQL encoding to apply.
   * @param string|null $owner The optional database owner to assign.
   * @param string|null $template The optional template database to clone from.
   * @return PostgreSQLCreateDatabaseStatement Returns the PostgreSQL CREATE DATABASE statement builder.
   */
  public function database(
    string $dbName,
    string $encoding = 'UTF8',
    ?string $owner = null,
    ?string $template = null,
  ): PostgreSQLCreateDatabaseStatement
  {
    return new PostgreSQLCreateDatabaseStatement(
      query: $this->query,
      dbName: $dbName,
      encoding: $encoding,
      owner: $owner,
      template: $template,
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
