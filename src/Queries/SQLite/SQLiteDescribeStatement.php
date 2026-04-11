<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Queries\Sql\SQLDescribeStatement;
use Assegai\Orm\Queries\Sql\SQLQuery;
use Assegai\Orm\Util\SqlDialectHelper;

/**
 * SQLite-specific describe statement builder.
 *
 * SQLite exposes table metadata through PRAGMA table_info.
 */
class SQLiteDescribeStatement extends SQLDescribeStatement
{
  /**
   * Create a SQLite describe statement.
   *
   * @param SQLQuery $query The query instance being built.
   * @param string $subject The table or view name to describe.
   */
  public function __construct(
    protected readonly SQLQuery $query,
    protected readonly string $subject,
  ) {
    $quotedSubject = SqlDialectHelper::quoteIdentifier($this->subject, SQLDialect::SQLITE);
    $this->query->setQueryString("PRAGMA table_info($quotedSubject)");
  }
}
