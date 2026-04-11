<?php

namespace Assegai\Orm\Queries\MariaDb;

use Assegai\Orm\Queries\MySql\MySQLAlterDefinition;

/**
 * MariaDB-specific ALTER builder.
 */
class MariaDbAlterDefinition extends MySQLAlterDefinition
{
  /**
   * Begin an ALTER TABLE statement using the MariaDB-specific table option builder.
   *
   * @param string $tableName The table being altered.
   * @return MariaDbAlterTableOption Returns the MariaDB alter-table option builder.
   */
  public function table(string $tableName): MariaDbAlterTableOption
  {
    $this->query->setQueryString(
      queryString: 'ALTER TABLE ' . $this->query->quoteIdentifier($tableName)
    );

    return new MariaDbAlterTableOption(query: $this->query);
  }
}