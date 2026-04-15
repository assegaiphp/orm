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
    $parts = $this->buildCreateDatabasePrefix();
    $parts[] = $this->buildDatabaseName();
    $parts = array_merge($parts, $this->buildOptionParts());

    return implode(' ', array_filter($parts, static fn(string $part): bool => $part !== ''));
  }

  /**
   * Builds the CREATE DATABASE prefix keywords.
   *
   * @return array<int, string> Returns the CREATE DATABASE prefix segments.
   */
  protected function buildCreateDatabasePrefix(): array
  {
    $parts = ['CREATE DATABASE'];

    if ($this->checkIfNotExists) {
      $parts[] = 'IF NOT EXISTS';
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
   * Builds the dialect-specific CREATE DATABASE options.
   *
   * @return array<int, string> Returns the option segments appended after the database name.
   */
  protected function buildOptionParts(): array
  {
    $parts = [];

    if ($this->defaultCharacterSet !== '') {
      $parts[] = 'CHARACTER SET ' . $this->defaultCharacterSet;
    }

    if ($this->defaultCollation !== '') {
      $parts[] = 'COLLATE ' . $this->defaultCollation;
    }

    if ($this->defaultEncryption) {
      $parts[] = "ENCRYPTION 'Y'";
    }

    return $parts;
  }
}
