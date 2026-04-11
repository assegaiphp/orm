<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Queries\Sql\SQLCreateDatabaseStatement;
use Assegai\Orm\Queries\Sql\SQLQuery;
use Assegai\Orm\Util\SqlIdentifier;

/**
 * PostgreSQL-specific CREATE DATABASE statement builder.
 */
class PostgreSQLCreateDatabaseStatement extends SQLCreateDatabaseStatement
{
  /**
   * Creates a PostgreSQL CREATE DATABASE statement builder.
   *
   * @param SQLQuery $query Receives the rendered CREATE DATABASE statement.
   * @param string $dbName The database name to create.
   * @param string $encoding The PostgreSQL encoding to apply.
   * @param string|null $owner The optional database owner to assign.
   * @param string|null $template The optional template database to clone from.
   */
  public function __construct(
    SQLQuery $query,
    string $dbName,
    protected readonly string $encoding = 'UTF8',
    protected readonly ?string $owner = null,
    protected readonly ?string $template = null,
  ) {
    parent::__construct(
      query: $query,
      dbName: $dbName,
      defaultCharacterSet: $encoding,
      defaultCollation: '',
      defaultEncryption: false,
      checkIfNotExists: false,
    );
  }

  /**
   * Builds the PostgreSQL CREATE DATABASE statement.
   *
   * @return string Returns the CREATE DATABASE statement tailored for PostgreSQL.
   */
  protected function buildQueryString(): string
  {
    $parts = [
      'CREATE DATABASE',
      SqlIdentifier::quote($this->dbName, $this->query->getDialect()),
    ];
    $withOptions = [];

    if ($this->encoding !== '') {
      $withOptions[] = "ENCODING '" . strtoupper($this->normalizeEncoding($this->encoding)) . "'";
    }

    if ($this->owner !== null && $this->owner !== '') {
      $withOptions[] = 'OWNER ' . SqlIdentifier::quote($this->owner, $this->query->getDialect());
    }

    if ($this->template !== null && $this->template !== '') {
      $withOptions[] = 'TEMPLATE ' . SqlIdentifier::quote($this->template, $this->query->getDialect());
    }

    if (!empty($withOptions)) {
      $parts[] = 'WITH ' . implode(' ', $withOptions);
    }

    return implode(' ', $parts);
  }

  /**
   * Normalizes framework-level encoding aliases to PostgreSQL encoding names.
   *
   * @param string $encoding The requested encoding alias.
   * @return string Returns a PostgreSQL-compatible encoding name.
   */
  private function normalizeEncoding(string $encoding): string
  {
    return match (strtoupper(str_replace(['-', '_'], '', $encoding))) {
      'UTF8MB4', 'UTF8MB3', 'UTF8' => 'UTF8',
      default => strtoupper($encoding),
    };
  }
}
