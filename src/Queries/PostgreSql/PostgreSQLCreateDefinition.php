<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Queries\Sql\SQLCreateDatabaseStatement;
use Assegai\Orm\Queries\Sql\SQLCreateTableStatement;
use Assegai\Orm\Queries\Sql\SQLDatabaseCreateDefinitionInterface;
use Assegai\Orm\Queries\Sql\SQLTableCreateDefinition;

/**
 * PostgreSQL-specific CREATE entry point.
 */
class PostgreSQLCreateDefinition extends SQLTableCreateDefinition implements SQLDatabaseCreateDefinitionInterface
{
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
    return parent::table($tableName, $isTemporary, $checkIfNotExists);
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
    return $this->createDatabaseStatement($dbName, $encoding, $owner, $template);
  }

  /**
   * Creates the PostgreSQL CREATE DATABASE statement builder.
   *
   * @param string $dbName The database name to create.
   * @param string $encoding The PostgreSQL encoding to apply.
   * @param string|null $owner The optional database owner to assign.
   * @param string|null $template The optional template database to clone from.
   * @return PostgreSQLCreateDatabaseStatement Returns the PostgreSQL CREATE DATABASE statement builder.
   */
  protected function createDatabaseStatement(
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
   * Creates the PostgreSQL CREATE TABLE statement builder.
   *
   * @param string $tableName The table name to create.
   * @param bool $isTemporary Indicates whether TEMPORARY should be emitted.
   * @param bool $checkIfNotExists Indicates whether IF NOT EXISTS should be emitted.
   * @return PostgreSQLCreateTableStatement Returns the PostgreSQL CREATE TABLE statement builder.
   */
  protected function createTableStatement(
    string $tableName,
    bool $isTemporary = false,
    bool $checkIfNotExists = true,
  ): SQLCreateTableStatement
  {
    return new PostgreSQLCreateTableStatement(
      query: $this->query,
      tableName: $tableName,
      isTemporary: $isTemporary,
      checkIfNotExists: $checkIfNotExists,
    );
  }
}
