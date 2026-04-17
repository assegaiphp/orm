<?php

namespace Assegai\Orm\Queries\MariaDb;

use Assegai\Orm\Queries\MySql\MySQLDropDefinition;
use Assegai\Orm\Queries\Sql\SQLDropDatabaseStatement;
use Assegai\Orm\Queries\Sql\SQLDropTableStatement;

/**
 * MariaDB-specific DROP entry point.
 */
class MariaDbDropDefinition extends MySQLDropDefinition
{
  /**
   * Begins a MariaDB DROP TABLE statement.
   *
   * @param string $tableName The table name to drop.
   * @return MariaDbDropTableStatement Returns the MariaDB DROP TABLE statement builder.
   */
  public function table(string $tableName): MariaDbDropTableStatement
  {
    return parent::table($tableName);
  }

  /**
   * Begins a MariaDB DROP DATABASE statement.
   *
   * @param string $dbName The database name to drop.
   * @return MariaDbDropDatabaseStatement Returns the MariaDB DROP DATABASE statement builder.
   */
  public function database(string $dbName): MariaDbDropDatabaseStatement
  {
    return parent::database($dbName);
  }

  /**
   * Creates the MariaDB DROP TABLE statement builder.
   *
   * @param string $tableName The table name to drop.
   * @return MariaDbDropTableStatement Returns the MariaDB DROP TABLE statement builder.
   */
  protected function createDropTableStatement(string $tableName): SQLDropTableStatement
  {
    return new MariaDbDropTableStatement(query: $this->query, tableName: $tableName);
  }

  /**
   * Creates the MariaDB DROP DATABASE statement builder.
   *
   * @param string $dbName The database name to drop.
   * @return MariaDbDropDatabaseStatement Returns the MariaDB DROP DATABASE statement builder.
   */
  protected function createDropDatabaseStatement(string $dbName): SQLDropDatabaseStatement
  {
    return new MariaDbDropDatabaseStatement(query: $this->query, dbName: $dbName);
  }
}
