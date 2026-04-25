<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Queries\Sql\SQLInsertIntoMultipleStatement;

/**
 * PostgreSQL-specific multi-row INSERT builder.
 */
class PostgreSQLInsertIntoMultipleStatement extends SQLInsertIntoMultipleStatement
{
  /**
   * Add a RETURNING clause to the INSERT statement.
   *
   * @param array|string $columns The columns to return from the inserted rows.
   * @return self Returns the current insert builder for fluent chaining.
   */
  public function returning(array|string $columns): self
  {
    $this->query->appendQueryString('RETURNING ' . $this->getColumnListString($columns));

    return $this;
  }
}
