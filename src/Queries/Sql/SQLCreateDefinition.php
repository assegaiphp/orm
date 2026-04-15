<?php

namespace Assegai\Orm\Queries\Sql;

/**
 * Shared CREATE entry point for SQL dialects that also support database creation.
 */
class SQLCreateDefinition extends SQLTableCreateDefinition implements SQLDatabaseCreateDefinitionInterface
{
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
    return $this->createDatabaseStatement(
      dbName: $dbName,
      defaultCharacterSet: $defaultCharacterSet,
      defaultCollation: $defaultCollation,
      defaultEncryption: $defaultEncryption,
    );
  }

  /**
   * Creates the CREATE DATABASE statement builder for the active SQL dialect.
   *
   * @param string $dbName The database name to create.
   * @param string $defaultCharacterSet The default character set to assign.
   * @param string $defaultCollation The default collation to assign.
   * @param bool $defaultEncryption Indicates whether database encryption should be enabled when supported.
   * @return SQLCreateDatabaseStatement Returns the CREATE DATABASE statement builder.
   */
  protected function createDatabaseStatement(
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
}
