<?php

namespace Assegai\Orm\Traits;

use Assegai\Orm\Queries\Sql\SQLKeyPart;
use Assegai\Orm\Queries\Sql\SQLLimitClause;
use Assegai\Orm\Util\SqlIdentifier;

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
      return $this->createLimitClause(limit: $limit, offset: $offset);
    }

    return $this;
  }

  /**
   * Create the LIMIT-clause builder used by this query segment.
   *
   * Dialect-specific builders override this method to keep the fluent
   * chain on their own typed LIMIT builders.
   *
   * @param int $limit The maximum number of rows to return.
   * @param int|null $offset The number of rows to skip before returning results.
   * @return SQLLimitClause Returns the LIMIT-clause builder.
   */
  protected function createLimitClause(int $limit, ?int $offset = null): SQLLimitClause
  {
    return new SQLLimitClause(query: $this->query, limit: $limit, offset: $offset);
  }

  /**
   * Order the results by the specified column names.
   *
   * @param array<string, string>|SQLKeyPart[] $keyParts A list of **SQLKeyPart** objects.
   * @return $this The current instance for method chaining.
   */
  public function orderBy(array $keyParts): static
  {
    $bufferKeyPart = $keyParts;

    if (property_exists($this, 'query')) {
      if (array_is_associative($keyParts)) {
        $callback = function ($key, $value) {
          return new SQLKeyPart(
            key: $key,
            ascending: strtoupper((string) $value) === 'ASC',
            dialect: $this->query->getDialect()
          );
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
      $queryString = "GROUP BY " . implode(', ', array_map(
        fn(string $columnName): string => SqlIdentifier::quote($columnName, $this->query->getDialect()),
        $columnNames
      ));
      $this->query->appendQueryString($queryString);
    }

    return $this;
  }
}
