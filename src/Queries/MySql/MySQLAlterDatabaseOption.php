<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\DataSource\SQLCharacterSet;
use Assegai\Orm\Queries\Sql\SQLQuery;
use Assegai\Orm\Traits\ExecutableTrait;

/**
 * Fluent option builder for MySQL ALTER DATABASE statements.
 *
 * This builder exposes only the database-level options supported by the
 * MySQL-family fluent path.
 */
class MySQLAlterDatabaseOption
{
  use ExecutableTrait;

  /**
   * Create a new ALTER DATABASE option builder.
   *
   * @param SQLQuery $query The query instance being configured.
   */
  public function __construct(private readonly SQLQuery $query)
  {
  }

  /**
   * Set the default character set for the database.
   *
   * @param SQLCharacterSet $characterSetName The character set to apply.
   * @return MySQLAlterDatabaseOption Returns the current builder for fluent chaining.
   */
  public function setCharacterSet(SQLCharacterSet $characterSetName): MySQLAlterDatabaseOption
  {
    $this->query->appendQueryString(tail: "DEFAULT CHARACTER SET = $characterSetName->value");

    return $this;
  }

  /**
   * Set the default collation for the database.
   *
   * @param string $collationName The collation name to apply.
   * @return MySQLAlterDatabaseOption Returns the current builder for fluent chaining.
   */
  public function setDefaultCollation(string $collationName): MySQLAlterDatabaseOption
  {
    $this->query->appendQueryString(tail: "DEFAULT COLLATE = $collationName");

    return $this;
  }

  /**
   * Set the default encryption flag for the database.
   *
   * @param bool $isEncrypted Whether encryption should be enabled.
   * @return MySQLAlterDatabaseOption Returns the current builder for fluent chaining.
   */
  public function setEncryption(bool $isEncrypted): MySQLAlterDatabaseOption
  {
    $value = $isEncrypted ? 'Y' : 'N';
    $this->query->appendQueryString(tail: "DEFAULT ENCRYPTION = $value");

    return $this;
  }

  /**
   * Set the read-only flag for the database.
   *
   * @param bool $isReadOnly Whether the database should be marked read only.
   * @return MySQLAlterDatabaseOption Returns the current builder for fluent chaining.
   */
  public function setReadOnly(bool $isReadOnly): MySQLAlterDatabaseOption
  {
    $value = $isReadOnly ? '1' : '0';
    $this->query->appendQueryString(tail: "READ ONLY = DEFAULT $value");

    return $this;
  }
}
