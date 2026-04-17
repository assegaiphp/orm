<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Queries\Sql\SQLCreateDatabaseStatement;
use Assegai\Orm\Queries\Sql\SQLCreateDefinition;
use Assegai\Orm\Queries\Sql\SQLCreateTableStatement;

/**
 * MySQL-specific CREATE entry point.
 */
class MySQLCreateDefinition extends SQLCreateDefinition
{
  /**
   * Begins a MySQL CREATE TABLE statement.
   *
   * @param string $tableName The table name to create.
   * @param bool $isTemporary Indicates whether TEMPORARY should be emitted.
   * @param bool $checkIfNotExists Indicates whether IF NOT EXISTS should be emitted.
   * @return MySQLCreateTableStatement Returns the MySQL CREATE TABLE statement builder.
   */
  public function table(
    string $tableName,
    bool $isTemporary = false,
    bool $checkIfNotExists = true,
  ): MySQLCreateTableStatement
  {
    return parent::table($tableName, $isTemporary, $checkIfNotExists);
  }

  /**
   * Begins a MySQL CREATE DATABASE statement.
   *
   * @param string $dbName The database name to create.
   * @param string $defaultCharacterSet The default character set for the database.
   * @param string $defaultCollation The default collation for the database.
   * @param bool $defaultEncryption Indicates whether MySQL encryption should be enabled.
   * @return MySQLCreateDatabaseStatement Returns the MySQL CREATE DATABASE statement builder.
   */
  public function database(
    string $dbName,
    string $defaultCharacterSet = 'utf8mb4',
    string $defaultCollation = 'utf8mb4_general_ci',
    bool $defaultEncryption = true,
  ): MySQLCreateDatabaseStatement
  {
    return parent::database($dbName, $defaultCharacterSet, $defaultCollation, $defaultEncryption);
  }

  /**
   * Creates the MySQL CREATE TABLE statement builder.
   *
   * @param string $tableName The table name to create.
   * @param bool $isTemporary Indicates whether TEMPORARY should be emitted.
   * @param bool $checkIfNotExists Indicates whether IF NOT EXISTS should be emitted.
   * @return MySQLCreateTableStatement Returns the MySQL CREATE TABLE statement builder.
   */
  protected function createTableStatement(
    string $tableName,
    bool $isTemporary = false,
    bool $checkIfNotExists = true,
  ): SQLCreateTableStatement
  {
    return new MySQLCreateTableStatement(
      query: $this->query,
      tableName: $tableName,
      isTemporary: $isTemporary,
      checkIfNotExists: $checkIfNotExists,
    );
  }

  /**
   * Creates the MySQL CREATE DATABASE statement builder.
   *
   * @param string $dbName The database name to create.
   * @param string $defaultCharacterSet The default character set for the database.
   * @param string $defaultCollation The default collation for the database.
   * @param bool $defaultEncryption Indicates whether MySQL encryption should be enabled.
   * @return MySQLCreateDatabaseStatement Returns the MySQL CREATE DATABASE statement builder.
   */
  protected function createDatabaseStatement(
    string $dbName,
    string $defaultCharacterSet = 'utf8mb4',
    string $defaultCollation = 'utf8mb4_general_ci',
    bool $defaultEncryption = true,
  ): SQLCreateDatabaseStatement
  {
    return new MySQLCreateDatabaseStatement(
      query: $this->query,
      dbName: $dbName,
      defaultCharacterSet: $defaultCharacterSet,
      defaultCollation: $defaultCollation,
      defaultEncryption: $defaultEncryption,
    );
  }
}
