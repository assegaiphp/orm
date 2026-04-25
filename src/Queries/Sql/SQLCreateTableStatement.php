<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Util\SqlIdentifier;

/**
 * Shared CREATE TABLE statement builder.
 */
class SQLCreateTableStatement
{
  /**
   * Creates a CREATE TABLE statement builder and primes the owning query string.
   *
   * @param SQLQuery $query Receives the rendered CREATE TABLE statement.
   * @param string $tableName The table to create.
   * @param bool $isTemporary Indicates whether TEMPORARY should be emitted.
   * @param bool $checkIfNotExists Indicates whether IF NOT EXISTS should be emitted.
   */
  public function __construct(
    protected readonly SQLQuery $query,
    protected readonly string $tableName,
    protected readonly bool $isTemporary = false,
    protected readonly bool $checkIfNotExists = true,
  ) {
    $this->query->setQueryString(queryString: $this->buildQueryString());
  }

  /**
   * Builds the CREATE TABLE prefix for the current query dialect.
   *
   * @return string Returns the CREATE TABLE query prefix.
   */
  protected function buildQueryString(): string
  {
    $parts = $this->buildCreateTablePrefix();
    $parts[] = $this->buildTableNameExpression();

    return implode(' ', array_filter($parts, static fn(string $part): bool => $part !== ''));
  }

  /**
   * Build the CREATE TABLE prefix for the current query dialect.
   *
   * @return array<int, string> Returns the CREATE TABLE prefix segments.
   */
  protected function buildCreateTablePrefix(): array
  {
    $parts = ['CREATE'];

    if ($this->isTemporary) {
      $parts[] = 'TEMPORARY';
    }

    $parts[] = 'TABLE';

    if ($this->checkIfNotExists) {
      $parts[] = 'IF NOT EXISTS';
    }

    return $parts;
  }

  /**
   * Build the quoted table identifier for the active dialect.
   *
   * @return string Returns the quoted table name expression.
   */
  protected function buildTableNameExpression(): string
  {
    return SqlIdentifier::quote($this->tableName, $this->query->getDialect());
  }

  /**
   * Appends the supplied column definitions to the CREATE TABLE statement.
   *
   * @param array<int, mixed> $columns The column definitions to render into the CREATE TABLE body.
   * @return SQLTableOptions Returns the table-options builder that finalizes the CREATE TABLE statement.
   */
  public function columns(array $columns): SQLTableOptions
  {
    return $this->createTableOptions(columns: $columns);
  }

  /**
   * Creates the table-options builder used by this CREATE TABLE statement.
   *
   * Dialect-specific CREATE TABLE builders override this method to keep the
   * fluent path on their own table-options type.
   *
   * @param array<int, mixed> $columns The column definitions to render into the CREATE TABLE body.
   * @return SQLTableOptions Returns the table-options builder for the active dialect.
   */
  protected function createTableOptions(array $columns): SQLTableOptions
  {
    return new SQLTableOptions(query: $this->query, columns: $columns);
  }
}
