<?php

namespace Assegai\Orm\Queries\Sql;

/**
 * Shared CREATE entry point for generic SQL query builders.
 */
class SQLCreateDefinition implements SQLCreateDefinitionInterface
{
  /**
   * Creates a shared CREATE definition bound to the supplied query root.
   *
   * @param SQLQuery $query Receives the rendered CREATE statement fragments.
   */
  public function __construct(protected SQLQuery $query)
  {
  }

  /**
   * Begins a CREATE TABLE statement.
   *
   * @param string $tableName The name of the table to create.
   * @param bool $isTemporary Indicates whether the table should be temporary.
   * @param bool $checkIfNotExists Indicates whether IF NOT EXISTS should be emitted.
   * @return SQLCreateTableStatement Returns the shared CREATE TABLE statement builder.
   */
  public function table(
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
   * Begins a CREATE DATABASE statement.
   *
   * @param string $dbName The database name to create.
   * @param string $defaultCharacterSet The default character set to assign.
   * @param string $defaultCollation The default collation to assign.
   * @param bool $defaultEncryption Indicates whether database encryption should be enabled when supported.
   * @return SQLCreateDatabaseStatement Returns the shared CREATE DATABASE statement builder.
   */
  public function database(
    string $dbName,
    string $defaultCharacterSet = 'utf8mb4',
    string $defaultCollation = 'utf8mb4_general_ci',
    bool $defaultEncryption = true,
  ): SQLCreateDatabaseStatement
  {
    return new SQLCreateDatabaseStatement(
      query: $this->query,
      dbName: $dbName,
      defaultCharacterSet: $defaultCharacterSet,
      defaultCollation: $defaultCollation,
      defaultEncryption: $defaultEncryption,
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
