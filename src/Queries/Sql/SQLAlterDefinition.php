<?php

namespace Assegai\Orm\Queries\Sql;

/**
 * The SQLAlterDefinition class provides methods that allow for the alteration of a database or a table.
 */
final readonly class SQLAlterDefinition
{
  /**
   * Constructs an instance of the SQLAlterDefinition.
   *
   * @param SQLQuery $query The SQLQuery instance used to construct queries.
   */
  public function __construct(private SQLQuery $query)
  {
  }

  /**
   * Returns an instance of SQLAlterDatabaseOption to alter the given database.
   *
   * @param string $databaseName The name of the database to alter.
   * @return SQLAlterDatabaseOption
   */
  public function database(string $databaseName): SQLAlterDatabaseOption
  {
    $this->query->setQueryString(queryString: "ALTER DATABASE `$databaseName`");
    return new SQLAlterDatabaseOption( query: $this->query );
  }

  /**
   * Returns an instance of SQLAlterTableOption to alter the given table.
   *
   * @param string $tableName The name of the table to alter.
   * @return SQLAlterTableOption
   */
  public function table(string $tableName): SQLAlterTableOption
  {
    $this->query->setQueryString(queryString: "ALTER TABLE `$tableName`");
    return new SQLAlterTableOption( query: $this->query );
  }
}
