<?php

namespace Assegai\Orm\Traits;

use Assegai\Orm\Queries\Sql\SQLKeyPart;
use Assegai\Orm\Queries\Sql\SQLLimitClause;

/**
 * SQLAggregatorTrait.
 *
 * @package Assegai\Orm\Traits
 */
trait SQLAggregatorTrait
{
  /**
   * Limit the number of results returned by the query.
   *
   * @param int $limit The maximum number of results to return.
   * @param int|null $offset The number of results to skip before starting to return results.
   * @return SQLLimitClause|$this The current instance for method chaining.
   */
  public function limit(int $limit, ?int $offset = null): SQLLimitClause|static
  {
    if (property_exists($this, 'query')) {
      return new SQLLimitClause(query: $this->query, limit: $limit, offset: $offset);
    }

    return $this;
  }

  /**
   * Order the results by the specified column names.
   *
   * @param array<string, string>|SQLKeyPart[] $keyParts A list of **SQLKeyPart** objects.
   * @return $this The current instance for method chaining.
   */
  public function orderBy(array $keyParts): static
  {
    $bufferKeyPart = [];

    if (property_exists($this, 'query')) {
      if (array_is_associative($keyParts)) {
        $callback = function ($key, $value) {
          return new SQLKeyPart($key, $value === 'ASC');
        };
        $bufferKeyPart = array_map($callback, array_keys($keyParts), $keyParts);
      }

      $queryString = "ORDER BY " . implode(', ', $bufferKeyPart);
      $this->query->appendQueryString($queryString);
    }

    return $this;
  }

  /**
   * Group the results by the specified column names.
   *
   * @param string[] $columnNames A list of column names to group by.
   * @return $this The current instance for method chaining.
   */
  public function groupBy(array $columnNames): static
  {
    if (property_exists($this, 'query')) {
      $queryString = "GROUP BY " . implode(', ', $columnNames);
      $this->query->appendQueryString($queryString);
    }

    return $this;
  }
}
