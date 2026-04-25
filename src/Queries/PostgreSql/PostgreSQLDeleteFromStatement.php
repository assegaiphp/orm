<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Queries\Sql\SQLDeleteFromStatement;

/**
 * PostgreSQL-specific DELETE builder.
 *
 * This builder exposes PostgreSQL-only clauses such as USING and RETURNING on
 * top of the shared SQL delete behaviour.
 */
class PostgreSQLDeleteFromStatement extends SQLDeleteFromStatement
{
  /**
   * Add a USING clause to the DELETE statement.
   *
   * @param array|string $tableReferences The tables to reference in the USING clause.
   * @return self Returns the current delete builder for fluent chaining.
   */
  public function using(array|string $tableReferences): self
  {
    $this->query->appendQueryString('USING ' . $this->formatTableReferences($tableReferences));

    return $this;
  }

  /**
   * Add a RETURNING clause to the DELETE statement.
   *
   * @param array|string $columns The columns to return from the deleted rows.
   * @return self Returns the current delete builder for fluent chaining.
   */
  public function returning(array|string $columns): self
  {
    $this->query->appendQueryString('RETURNING ' . $this->getColumnListString($columns));

    return $this;
  }
}