<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Traits\ExecutableTrait;

/**
 * Base SQL-family describe statement builder.
 *
 * Dialect-specific subclasses can override the constructor to emit the syntax
 * that matches their own metadata APIs while still remaining covariant with
 * the shared SQL query root.
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
    $this->query->setQueryString("DESCRIBE $this->subject");
  }
}
