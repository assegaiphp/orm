<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Queries\Sql\SQLDropDatabaseStatement;
use Assegai\Orm\Queries\Sql\SQLDropDefinition;
use Assegai\Orm\Queries\Sql\SQLDropTableStatement;

/**
 * MySQL-specific DROP entry point.
 */
class MySQLDropDefinition extends SQLDropDefinition
{
  /**
   * Begins a MySQL DROP DATABASE statement.
   *
   * @param string $dbName The database name to drop.
   * @return MySQLDropDatabaseStatement Returns the MySQL DROP DATABASE statement builder.
   */
  public function database(string $dbName): MySQLDropDatabaseStatement
  {
    return $this->createDropDatabaseStatement(dbName: $dbName);
  }

  /**
   * Begins a MySQL DROP TABLE statement.
   *
   * @param string $tableName The table name to drop.
   * @return MySQLDropTableStatement Returns the MySQL DROP TABLE statement builder.
   */
  public function table(string $tableName): MySQLDropTableStatement
  {
    return $this->createDropTableStatement(tableName: $tableName);
  }

  /**
   * Creates the MySQL DROP DATABASE statement builder.
   *
   * @param string $dbName The database name to drop.
   * @return MySQLDropDatabaseStatement Returns the MySQL DROP DATABASE statement builder.
   */
  protected function createDropDatabaseStatement(string $dbName): SQLDropDatabaseStatement
  {
    return new MySQLDropDatabaseStatement(query: $this->query, dbName: $dbName);
  }

  /**
   * Creates the MySQL DROP TABLE statement builder.
   *
   * @param string $tableName The table name to drop.
   * @return MySQLDropTableStatement Returns the MySQL DROP TABLE statement builder.
   */
  protected function createDropTableStatement(string $tableName): SQLDropTableStatement
  {
    return new MySQLDropTableStatement(query: $this->query, tableName: $tableName);
  }
}
