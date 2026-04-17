<?php

namespace Assegai\Orm\Queries\MariaDb;

use Assegai\Orm\Queries\MySql\MySQLCreateDefinition;
use Assegai\Orm\Queries\Sql\SQLCreateDatabaseStatement;
use Assegai\Orm\Queries\Sql\SQLCreateTableStatement;

/**
 * MariaDB-specific CREATE entry point.
 */
class MariaDbCreateDefinition extends MySQLCreateDefinition
{
  /**
   * Begins a MariaDB CREATE DATABASE statement.
   *
   * @param string $dbName The database name to create.
   * @param string $defaultCharacterSet The default character set for the database.
   * @param string $defaultCollation The default collation for the database.
   * @param bool $defaultEncryption Indicates whether MariaDB encryption should be enabled.
   * @return MariaDbCreateDatabaseStatement Returns the MariaDB CREATE DATABASE statement builder.
   */
  public function database(
    string $dbName,
    string $defaultCharacterSet = 'utf8mb4',
    string $defaultCollation = 'utf8mb4_general_ci',
    bool $defaultEncryption = true,
  ): MariaDbCreateDatabaseStatement
  {
    return parent::database($dbName, $defaultCharacterSet, $defaultCollation, $defaultEncryption);
  }

  /**
   * Begins a MariaDB CREATE TABLE statement.
   *
   * @param string $tableName The table name to create.
   * @param bool $isTemporary Indicates whether TEMPORARY should be emitted.
   * @param bool $checkIfNotExists Indicates whether IF NOT EXISTS should be emitted.
   * @return MariaDbCreateTableStatement Returns the MariaDB CREATE TABLE statement builder.
   */
  public function table(
    string $tableName,
    bool $isTemporary = false,
    bool $checkIfNotExists = true,
  ): MariaDbCreateTableStatement
  {
    return parent::table($tableName, $isTemporary, $checkIfNotExists);
  }

  /**
   * Creates the MariaDB CREATE TABLE statement builder.
   *
   * @param string $tableName The table name to create.
   * @param bool $isTemporary Indicates whether TEMPORARY should be emitted.
   * @param bool $checkIfNotExists Indicates whether IF NOT EXISTS should be emitted.
   * @return MariaDbCreateTableStatement Returns the MariaDB CREATE TABLE statement builder.
   */
  protected function createTableStatement(
    string $tableName,
    bool $isTemporary = false,
    bool $checkIfNotExists = true,
  ): SQLCreateTableStatement
  {
    return new MariaDbCreateTableStatement(
      query: $this->query,
      tableName: $tableName,
      isTemporary: $isTemporary,
      checkIfNotExists: $checkIfNotExists,
    );
  }

  /**
   * Creates the MariaDB CREATE DATABASE statement builder.
   *
   * @param string $dbName The database name to create.
   * @param string $defaultCharacterSet The default character set for the database.
   * @param string $defaultCollation The default collation for the database.
   * @param bool $defaultEncryption Indicates whether MariaDB encryption should be enabled.
   * @return MariaDbCreateDatabaseStatement Returns the MariaDB CREATE DATABASE statement builder.
   */
  protected function createDatabaseStatement(
    string $dbName,
    string $defaultCharacterSet = 'utf8mb4',
    string $defaultCollation = 'utf8mb4_general_ci',
    bool $defaultEncryption = true,
  ): SQLCreateDatabaseStatement
  {
    return new MariaDbCreateDatabaseStatement(
      query: $this->query,
      dbName: $dbName,
      defaultCharacterSet: $defaultCharacterSet,
      defaultCollation: $defaultCollation,
      defaultEncryption: $defaultEncryption,
    );
  }
}
