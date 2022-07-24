<?php

namespace Assegai\Orm\Queries\Sql;

final class SQLDropDefinition
{
  /**
   * @param SQLQuery $query
   */
  public function __construct(private readonly SQLQuery $query) { }

  /**
   * @param string $dbName
   * @return SQLDropDatabaseStatement
   */
  public function database(string $dbName): SQLDropDatabaseStatement
  {
    return new SQLDropDatabaseStatement( query: $this->query, dbName: $dbName );
  }

  /**
   * @param string $tableName
   * @return SQLDropTableStatement
   */
  public function table(string $tableName): SQLDropTableStatement
  {
    return new SQLDropTableStatement( query: $this->query, tableName: $tableName );
  }
}