<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Management\Options\FindOptions;
use Assegai\Orm\Management\Options\FindWhereOptions;
use Assegai\Orm\Traits\ExecutableTrait;
use Assegai\Orm\Traits\JoinableTrait;
use Assegai\Orm\Traits\SQLAggregatorTrait;

/**
 * Base FROM-clause builder shared across SQL-family dialects.
 *
 * The class is intentionally extensible so dialect-specific subclasses can
 * keep the fluent chain typed without duplicating the shared FROM rendering.
 */
class SQLTableReference
{
  use ExecutableTrait;
  use SQLAggregatorTrait;
  use JoinableTrait;

  /**
   * Create a new shared table reference builder.
   *
   * @param SQLQuery $query The query being built.
   * @param array|string $tableReferences The table name, table list, or alias map.
   */
  public function __construct(
    protected readonly SQLQuery $query,
    protected readonly array|string $tableReferences
  ) {
    $queryString = 'FROM ';
    $separate = ', ';

    if (is_string($tableReferences))
    {
      $queryString .= $this->query->quoteIdentifier($tableReferences);
    }
    else
    {
      foreach ($tableReferences as $alias => $reference)
      {
        if (is_numeric($alias))
        {
          $queryString .= $this->query->quoteIdentifier((string)$reference) . $separate;
        }
        else
        {
          $queryString .= $this->query->quoteIdentifier((string)$reference) . ' AS ' . $this->query->quoteIdentifier((string)$alias) . $separate;
        }
      }
      $queryString = trim($queryString, $separate);
    }
    $this->query->appendQueryString(tail: $queryString);
  }

  /**
   * Add a WHERE clause to the current query.
   *
   * @param string|array|FindOptions|FindWhereOptions $condition The condition to compile and append.
   * @return SQLWhereClause Returns the shared WHERE clause builder.
   */
  public function where(string|array|FindOptions|FindWhereOptions $condition): SQLWhereClause
  {
    return new SQLWhereClause(
      query: $this->query,
      condition: $condition
    );
  }

  /**
   * Add a HAVING clause to the current query.
   *
   * @param string $condition The HAVING condition to append.
   * @return SQLHavingClause Returns the shared HAVING clause builder.
   */
  public function having(string $condition): SQLHavingClause
  {
    return new SQLHavingClause(
      query: $this->query,
      condition: $condition
    );
  }
}
