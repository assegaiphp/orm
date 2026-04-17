<?php

namespace Assegai\Orm\Queries\Sql;

/**
 * Base fluent builder for ALTER TABLE statements shared across the SQL family.
 *
 * Dialect-specific subclasses may extend this builder with additional fluent
 * entrypoints when their backend supports them, such as MySQL-family database
 * alteration helpers.
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
   * Begin an ALTER TABLE statement.
   *
   * @param string $tableName The table being altered.
   * @return SQLAlterTableOption Returns the base alter-table option builder.
   */
  public function table(string $tableName): SQLAlterTableOption
  {
    $this->beginAlterTable(tableName: $tableName);

    return $this->createAlterTableOption();
  }

  /**
   * Start the ALTER TABLE statement for the given table.
   *
   * @param string $tableName The table being altered.
   * @return void
   */
  protected function beginAlterTable(string $tableName): void
  {
    $this->query->setQueryString(
      queryString: 'ALTER TABLE ' . $this->query->quoteIdentifier($tableName)
    );
  }

  /**
   * Create the alter-table option builder for this dialect.
   *
   * Dialect-specific subclasses override this method to keep the fluent path
   * on their own typed alter-table option builders.
   *
   * @return SQLAlterTableOption Returns the alter-table option builder.
   */
  protected function createAlterTableOption(): SQLAlterTableOption
  {
    return new SQLAlterTableOption(query: $this->query);
  }
}
