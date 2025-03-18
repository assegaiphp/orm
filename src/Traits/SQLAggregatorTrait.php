<?php

namespace Assegai\Orm\Traits;

use Assegai\Orm\Queries\Sql\SQLKeyPart;
use Assegai\Orm\Queries\Sql\SQLLimitClause;

trait SQLAggregatorTrait
{
  /**
   * @param int $limit
   * @param int|null $offset
   * @return SQLLimitClause|$this
   */
  public function limit(int $limit, ?int $offset = null): SQLLimitClause|static
  {
    if (property_exists($this, 'query'))
    {
      return new SQLLimitClause(
        query: $this->query,
        limit: $limit,
        offset: $offset
      );
    }

    return $this;
  }

  /**
   * @param array<string, string>|SQLKeyPart[] $keyParts A list of **SQLKeyPart** objects.
   */
  public function orderBy(array $keyParts): static
  {
    if (property_exists($this, 'query'))
    {
      if (array_is_associative($keyParts)) {
        $keyParts = array_map(function ($key, $value) {
          return new SQLKeyPart($key, $value);
        }, array_keys($keyParts), $keyParts);
      }
      $queryString = "ORDER BY " . implode(', ', $keyParts);
      $this->query->appendQueryString($queryString);
    }
    return $this;
  }

  /**
   * @param array $columnNames
   * @return $this
   */
  public function groupBy(array $columnNames): static
  {
    if (property_exists($this, 'query'))
    {
      $queryString = "GROUP BY " . implode(', ', $columnNames);
      $this->query->appendQueryString($queryString);
    }
    return $this;
  }
}
