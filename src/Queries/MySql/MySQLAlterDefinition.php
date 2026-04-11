<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Queries\Sql\SQLAlterDefinition;

/**
 * MySQL-specific ALTER builder.
 */
class MySQLAlterDefinition extends SQLAlterDefinition
{
  /**
   * Begin an ALTER TABLE statement using the MySQL-specific table option builder.
   *
   * @param string $tableName The table being altered.
   * @return MySQLAlterTableOption Returns the MySQL alter-table option builder.
   */
  public function table(string $tableName): MySQLAlterTableOption
  {
    $this->query->setQueryString(
      queryString: 'ALTER TABLE ' . $this->query->quoteIdentifier($tableName)
    );

    return new MySQLAlterTableOption(query: $this->query);
  }
}