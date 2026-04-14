<?php

namespace Assegai\Orm\Queries\MariaDb;

use Assegai\Orm\Queries\MySql\MySQLCreateDefinition;

/**
 * MariaDB-specific CREATE entry point.
 */
class MariaDbCreateDefinition extends MySQLCreateDefinition
{
  /**
   * Begins a MariaDB CREATE TABLE statement.
   *
   * @param string $tableName The table name to create.
   * @param bool $isTemporary Indicates whether TEMPORARY should be emitted.
   * @param bool $checkIfNotExists Indicates whether IF NOT EXISTS should be emitted.
   * @return MariaDbCreateTableStatement Returns the MariaDB CREATE TABLE statement builder.
   */
  public function table(
    string $tableName,
    bool $isTemporary = false,
    bool $checkIfNotExists = true,
  ): MariaDbCreateTableStatement
  {
    return new MariaDbCreateTableStatement(
      query: $this->query,
      tableName: $tableName,
      isTemporary: $isTemporary,
      checkIfNotExists: $checkIfNotExists,
    );
  }
}
