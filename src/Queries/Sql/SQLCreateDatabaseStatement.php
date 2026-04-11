<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Traits\ExecutableTrait;
use Assegai\Orm\Util\SqlIdentifier;

/**
 * Shared CREATE DATABASE statement builder.
 */
class SQLCreateDatabaseStatement
{
  use ExecutableTrait;

  /**
   * Creates a CREATE DATABASE statement builder and primes the owning query string.
   *
   * @param SQLQuery $query Receives the rendered CREATE DATABASE statement.
   * @param string $dbName The database name to create.
   * @param string $defaultCharacterSet The preferred character set for dialects that support it.
   * @param string $defaultCollation The preferred collation for dialects that support it.
   * @param bool $defaultEncryption Indicates whether encryption should be enabled when supported.
   * @param bool $checkIfNotExists Indicates whether IF NOT EXISTS should be emitted when supported.
   */
  public function __construct(
    protected readonly SQLQuery $query,
    protected readonly string $dbName,
    protected readonly string $defaultCharacterSet = 'utf8mb4',
    protected readonly string $defaultCollation = 'utf8mb4_general_ci',
    protected readonly bool $defaultEncryption = true,
    protected readonly bool $checkIfNotExists = true,
  ) {
    $this->query->setQueryString(queryString: $this->buildQueryString());
  }

  /**
   * Builds the CREATE DATABASE statement for the current SQL dialect.
   *
   * @return string Returns the CREATE DATABASE statement.
   */
  protected function buildQueryString(): string
  {
    $parts = ['CREATE DATABASE'];

    if ($this->checkIfNotExists) {
      $parts[] = 'IF NOT EXISTS';
    }

    $parts[] = SqlIdentifier::quote($this->dbName, $this->query->getDialect());

    if ($this->defaultCharacterSet !== '') {
      $parts[] = 'CHARACTER SET ' . $this->defaultCharacterSet;
    }

    if ($this->defaultCollation !== '') {
      $parts[] = 'COLLATE ' . $this->defaultCollation;
    }

    if ($this->defaultEncryption) {
      $parts[] = "ENCRYPTION 'Y'";
    }

    return implode(' ', $parts);
  }
}
