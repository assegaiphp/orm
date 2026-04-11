<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Queries\Sql\SQLAlterDefinition;

/**
 * PostgreSQL-specific ALTER builder.
 */
class PostgreSQLAlterDefinition extends SQLAlterDefinition
{
  /**
   * Begin an ALTER TABLE statement using the PostgreSQL-specific table option builder.
   *
   * @param string $tableName The table being altered.
   * @return PostgreSQLAlterTableOption Returns the PostgreSQL alter-table option builder.
   */
  public function table(string $tableName): PostgreSQLAlterTableOption
  {
    $this->query->setQueryString(
      queryString: 'ALTER TABLE ' . $this->query->quoteIdentifier($tableName)
    );

    return new PostgreSQLAlterTableOption(query: $this->query);
  }
}