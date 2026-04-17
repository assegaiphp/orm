<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Queries\Sql\SQLInsertIntoStatement;

/**
 * PostgreSQL-specific single-row INSERT builder.
 */
class PostgreSQLInsertIntoStatement extends SQLInsertIntoStatement
{
  /**
   * Add a RETURNING clause to the INSERT statement.
   *
   * @param array|string $columns The columns to return from the inserted row.
   * @return self Returns the current insert builder for fluent chaining.
   */
  public function returning(array|string $columns): self
  {
    $this->query->appendQueryString('RETURNING ' . $this->getColumnListString($columns));

    return $this;
  }
}
