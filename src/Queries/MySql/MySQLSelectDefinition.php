<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Queries\Sql\SQLSelectDefinition;

/**
 * MySQL-specific SELECT builder.
 */
class MySQLSelectDefinition extends SQLSelectDefinition
{
  /**
   * Mark the SELECT statement as HIGH_PRIORITY.
   *
   * @return self Returns the current select builder for fluent chaining.
   */
  public function highPriority(): self
  {
    if (!str_starts_with($this->query->queryString(), 'SELECT HIGH_PRIORITY ')) {
      $this->query->setQueryString(
        str_replace('SELECT ', 'SELECT HIGH_PRIORITY ', $this->query->queryString())
      );
    }

    return $this;
  }
}