<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Queries\Sql\SQLDescribeStatement;
use Assegai\Orm\Queries\Sql\SQLQuery;
use Assegai\Orm\Util\SqlDialectHelper;

/**
 * MySQL-specific DESCRIBE statement builder.
 */
class MySQLDescribeStatement extends SQLDescribeStatement
{
  /**
   * Create a MySQL DESCRIBE statement.
   *
   * @param SQLQuery $query The query instance being built.
   * @param string $subject The table or view name to describe.
   */
  public function __construct(
    protected readonly SQLQuery $query,
    protected readonly string $subject,
  ) {
    $quotedSubject = SqlDialectHelper::quoteIdentifier($this->subject, SQLDialect::MYSQL);
    $this->query->setQueryString("DESCRIBE $quotedSubject");
  }
}
