<?php

namespace Assegai\Orm\Queries\Sql;

/**
 * Base fluent builder for ALTER statements.
 *
 * This builder provides the shared SQL family entrypoints for altering
 * databases and tables, while allowing dialect-specific subclasses to return
 * typed alter-table builders.
 */
class SQLAlterDefinition
{
  /**
   * Create a new ALTER builder.
   *
   * @param SQLQuery $query The query instance being built.
   */
  public function __construct(protected readonly SQLQuery $query)
  {
  }

  /**
   * Begin an ALTER DATABASE statement.
   *
   * @param string $databaseName The database being altered.
   * @return SQLAlterDatabaseOption Returns the database alter option builder.
   */
  public function database(string $databaseName): SQLAlterDatabaseOption
  {
    $this->query->setQueryString(
      queryString: 'ALTER DATABASE ' . $this->query->quoteIdentifier($databaseName)
    );

    return new SQLAlterDatabaseOption(query: $this->query);
  }

  /**
   * Begin an ALTER TABLE statement.
   *
   * @param string $tableName The table being altered.
   * @return SQLAlterTableOption Returns the base alter-table option builder.
   */
  public function table(string $tableName): SQLAlterTableOption
  {
    $this->query->setQueryString(
      queryString: 'ALTER TABLE ' . $this->query->quoteIdentifier($tableName)
    );

    return new SQLAlterTableOption(query: $this->query);
  }
}