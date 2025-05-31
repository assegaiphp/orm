<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Traits\ExecutableTrait;
use Assegai\Orm\Traits\JoinableTrait;
use Assegai\Orm\Traits\SQLAggregatorTrait;

final class SQLTableReference
{
  use ExecutableTrait;
  use SQLAggregatorTrait;
  use JoinableTrait;

  /**
   * @param SQLQuery $query
   * @param array|string $tableReferences
   */
  public function __construct(
    private readonly SQLQuery $query,
    private readonly array|string $tableReferences
  ) {
    $queryString = "FROM ";
    $separate = ', ';

    if (is_string($tableReferences))
    {
      $queryString .= "`$tableReferences`";
    }
    else
    {
      foreach ($tableReferences as $alias => $reference)
      {
        if (is_numeric($alias))
        {
          # We don't have an alias
          $queryString .= "`{$reference}`{$separate}";
        }
        else
        {
          $queryString .= "`{$reference}` AS {$alias}{$separate}";
        }
      }
      $queryString = trim($queryString, $separate);
    }
    $this->query->appendQueryString(tail: $queryString);
  }

  /**
   * @param string $condition
   * @return SQLWhereClause
   */
  public function where(string $condition): SQLWhereClause
  {
    return new SQLWhereClause(
      query: $this->query,
      condition: $condition
    );
  }

  /**
   * @param string $condition
   * @return SQLHavingClause
   */
  public function having(string $condition): SQLHavingClause
  {
    return new SQLHavingClause(
      query: $this->query,
      condition: $condition
    );
  }
}