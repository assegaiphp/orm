<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Traits\ExecutableTrait;
use Assegai\Orm\Util\SqlIdentifier;

/**
 * Shared DROP TABLE statement builder.
 */
class SQLDropTableStatement
{
  use ExecutableTrait;

  /**
   * Creates a DROP TABLE statement builder and primes the owning query string.
   *
   * @param SQLQuery $query Receives the rendered DROP TABLE statement.
   * @param string $tableName The table name to drop.
   * @param bool $checkIfExists Indicates whether IF EXISTS should be emitted.
   */
  public function __construct(
    protected readonly SQLQuery $query,
    protected readonly string $tableName,
    protected readonly bool $checkIfExists = true,
  ) {
    $this->query->setQueryString(queryString: $this->buildQueryString());
  }

  /**
   * Builds the DROP TABLE statement for the current SQL dialect.
   *
   * @return string Returns the DROP TABLE statement.
   */
  protected function buildQueryString(): string
  {
    $parts = ['DROP TABLE'];

    if ($this->checkIfExists) {
      $parts[] = 'IF EXISTS';
    }

    $parts[] = SqlIdentifier::quote($this->tableName, $this->query->getDialect());

    return implode(' ', $parts);
  }
}
