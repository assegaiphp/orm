<?php

namespace Assegai\Orm\Queries\MsSql;

use Assegai\Orm\Queries\Sql\SQLDropDatabaseStatement;
use Assegai\Orm\Queries\Sql\SQLDropDefinition;
use Assegai\Orm\Queries\Sql\SQLDropTableStatement;

/**
 * MSSQL-specific DROP entry point.
 */
class MsSqlDropDefinition extends SQLDropDefinition
{
  /**
   * Begin a DROP TABLE statement using MSSQL-specific fluent builders.
   *
   * @param string $tableName The table name to drop.
   * @return MsSqlDropTableStatement Returns the MSSQL drop-table builder.
   */
  public function table(string $tableName): MsSqlDropTableStatement
  {
    return parent::table($tableName);
  }

  /**
   * Begin a DROP DATABASE statement using MSSQL-specific fluent builders.
   *
   * @param string $dbName The database name to drop.
   * @param bool $checkIfExists Indicates whether the statement should guard for existence.
   * @return MsSqlDropDatabaseStatement Returns the MSSQL drop-database builder.
   */
  public function database(string $dbName, bool $checkIfExists = false): MsSqlDropDatabaseStatement
  {
    return $this->createDropDatabaseStatement($dbName, $checkIfExists);
  }

  /**
   * Create the MSSQL DROP TABLE statement builder.
   *
   * @param string $tableName The table name to drop.
   * @return SQLDropTableStatement Returns the MSSQL drop-table builder.
   */
  protected function createDropTableStatement(string $tableName): SQLDropTableStatement
  {
    return new MsSqlDropTableStatement(query: $this->query, tableName: $tableName);
  }

  /**
   * Create the MSSQL DROP DATABASE statement builder.
   *
   * @param string $dbName The database name to drop.
   * @param bool $checkIfExists Indicates whether the statement should guard for existence.
   * @return SQLDropDatabaseStatement Returns the MSSQL drop-database builder.
   */
  protected function createDropDatabaseStatement(string $dbName, bool $checkIfExists = false): SQLDropDatabaseStatement
  {
    return new MsSqlDropDatabaseStatement(query: $this->query, dbName: $dbName, checkIfExists: $checkIfExists);
  }
}
