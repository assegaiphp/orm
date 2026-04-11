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
    $this->query->setQueryString(
      queryString: 'ALTER TABLE ' . $this->query->quoteIdentifier($tableName)
    );

    return new SQLAlterTableOption(query: $this->query);
  }
}
