<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Traits\ExecutableTrait;
use Assegai\Orm\Util\SqlIdentifier;

/**
 * Shared DROP DATABASE statement builder.
 */
class SQLDropDatabaseStatement
{
  use ExecutableTrait;

  /**
   * Creates a DROP DATABASE statement builder and primes the owning query string.
   *
   * @param SQLQuery $query Receives the rendered DROP DATABASE statement.
   * @param string $dbName The database name to drop.
   * @param bool $checkIfExists Indicates whether IF EXISTS should be emitted.
   */
  public function __construct(
    protected readonly SQLQuery $query,
    protected readonly string $dbName,
    protected readonly bool $checkIfExists = false,
  ) {
    $this->query->setQueryString(queryString: $this->buildQueryString());
  }

  /**
   * Builds the DROP DATABASE statement for the current SQL dialect.
   *
   * @return string Returns the DROP DATABASE statement.
   */
  protected function buildQueryString(): string
  {
    $parts = $this->buildDropDatabasePrefix();
    $parts[] = $this->buildDatabaseName();
    $parts = array_merge($parts, $this->buildTrailingParts());

    return implode(' ', array_filter($parts, static fn(string $part): bool => $part !== ''));
  }

  /**
   * Builds the DROP DATABASE prefix keywords.
   *
   * @return array<int, string> Returns the DROP DATABASE prefix segments.
   */
  protected function buildDropDatabasePrefix(): array
  {
    $parts = ['DROP DATABASE'];

    if ($this->checkIfExists) {
      $parts[] = 'IF EXISTS';
    }

    return $parts;
  }

  /**
   * Builds the quoted database identifier for the active dialect.
   *
   * @return string Returns the quoted database name segment.
   */
  protected function buildDatabaseName(): string
  {
    return SqlIdentifier::quote($this->dbName, $this->query->getDialect());
  }

  /**
   * Builds trailing DROP DATABASE options for dialects that support them.
   *
   * @return array<int, string> Returns trailing option segments appended after the database name.
   */
  protected function buildTrailingParts(): array
  {
    return [];
  }
}
