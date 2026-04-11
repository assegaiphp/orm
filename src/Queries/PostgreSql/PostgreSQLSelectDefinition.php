<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Queries\Sql\SQLSelectDefinition;

/**
 * PostgreSQL-specific SELECT builder.
 */
class PostgreSQLSelectDefinition extends SQLSelectDefinition
{
  /**
   * Add a DISTINCT ON clause to the SELECT statement.
   *
   * @param array $columns The columns used by DISTINCT ON.
   * @return self Returns the current select builder for fluent chaining.
   */
  public function distinctOn(array $columns): self
  {
    $columnList = $this->getColumnListString($columns);
    $this->query->appendQueryString("DISTINCT ON ($columnList)");

    return $this;
  }
}