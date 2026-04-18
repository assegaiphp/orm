<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Traits\ExecutableTrait;
use Assegai\Orm\Util\SqlIdentifier;

/**
 * Base SQL-family describe statement builder.
 *
 * Dialect-specific subclasses can override the rendering hooks to emit the
 * syntax that matches their own metadata APIs while still remaining covariant
 * with the shared SQL query root.
 */
class SQLDescribeStatement
{
  use ExecutableTrait;

  /**
   * Create a generic DESCRIBE statement.
   *
   * @param SQLQuery $query The query instance being built.
   * @param string $subject The table or view name to describe.
   */
  public function __construct(
    protected readonly SQLQuery $query,
    protected readonly string $subject,
  ) {
    $this->query->setQueryString($this->buildQueryString());
  }

  /**
   * Build the describe statement for the active SQL-family builder.
   *
   * @return string Returns the rendered describe statement.
   */
  protected function buildQueryString(): string
  {
    return 'DESCRIBE ' . $this->buildSubjectExpression();
  }

  /**
   * Build the subject expression for the active SQL dialect.
   *
   * @return string Returns the quoted table or view identifier.
   */
  protected function buildSubjectExpression(): string
  {
    return SqlIdentifier::quote($this->subject, $this->query->getDialect());
  }
}
