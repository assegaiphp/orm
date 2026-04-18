<?php

namespace Assegai\Orm\Queries\MsSql;

use Assegai\Orm\Queries\Sql\SQLCreateDatabaseStatement;
use Assegai\Orm\Queries\Sql\SQLCreateDefinition;
use Assegai\Orm\Queries\Sql\SQLCreateTableStatement;

/**
 * MSSQL-specific CREATE entry point.
 */
class MsSqlCreateDefinition extends SQLCreateDefinition
{
  /**
   * Begin a CREATE TABLE statement using MSSQL-specific fluent builders.
   *
   * @param string $tableName The table name to create.
   * @param bool $isTemporary Indicates whether the table should be temporary.
   * @param bool $checkIfNotExists Indicates whether the statement should guard for existence.
   * @return MsSqlCreateTableStatement Returns the MSSQL create-table builder.
   */
  public function table(
    string $tableName,
    bool $isTemporary = false,
    bool $checkIfNotExists = true,
  ): MsSqlCreateTableStatement
  {
    return parent::table($tableName, $isTemporary, $checkIfNotExists);
  }

  /**
   * Begin a CREATE DATABASE statement using MSSQL-specific fluent builders.
   *
   * @param string $dbName The database name to create.
   * @param string $defaultCharacterSet Unused for MSSQL.
   * @param string $defaultCollation Unused for MSSQL.
   * @param bool $defaultEncryption Unused for MSSQL.
   * @return MsSqlCreateDatabaseStatement Returns the MSSQL create-database builder.
   */
  public function database(
    string $dbName,
    string $defaultCharacterSet = 'utf8mb4',
    string $defaultCollation = 'utf8mb4_general_ci',
    bool $defaultEncryption = true,
  ): MsSqlCreateDatabaseStatement
  {
    return parent::database($dbName, $defaultCharacterSet, $defaultCollation, $defaultEncryption);
  }

  /**
   * Create the MSSQL CREATE TABLE statement builder.
   *
   * @param string $tableName The table name to create.
   * @param bool $isTemporary Indicates whether the table should be temporary.
   * @param bool $checkIfNotExists Indicates whether the statement should guard for existence.
   * @return SQLCreateTableStatement Returns the MSSQL create-table builder.
   */
  protected function createTableStatement(
    string $tableName,
    bool $isTemporary = false,
    bool $checkIfNotExists = true,
  ): SQLCreateTableStatement
  {
    return new MsSqlCreateTableStatement(
      query: $this->query,
      tableName: $tableName,
      isTemporary: $isTemporary,
      checkIfNotExists: $checkIfNotExists,
    );
  }

  /**
   * Create the MSSQL CREATE DATABASE statement builder.
   *
   * @param string $dbName The database name to create.
   * @param string $defaultCharacterSet Unused for MSSQL.
   * @param string $defaultCollation Unused for MSSQL.
   * @param bool $defaultEncryption Unused for MSSQL.
   * @return SQLCreateDatabaseStatement Returns the MSSQL create-database builder.
   */
  protected function createDatabaseStatement(
    string $dbName,
    string $defaultCharacterSet = 'utf8mb4',
    string $defaultCollation = 'utf8mb4_general_ci',
    bool $defaultEncryption = true,
  ): SQLCreateDatabaseStatement
  {
    return new MsSqlCreateDatabaseStatement(
      query: $this->query,
      dbName: $dbName,
      defaultCharacterSet: $defaultCharacterSet,
      defaultCollation: $defaultCollation,
      defaultEncryption: $defaultEncryption,
    );
  }
}
