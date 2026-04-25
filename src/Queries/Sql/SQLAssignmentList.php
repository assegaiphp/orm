<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Management\Options\FindOptions;
use Assegai\Orm\Management\Options\FindWhereOptions;
use Assegai\Orm\Traits\ExecutableTrait;

/**
 * Base SET-clause builder shared across SQL-family dialects.
 *
 * Dialect-specific subclasses keep the fluent chain typed after
 * `update(...)->set(...)`.
 */
class SQLAssignmentList
{
  use ExecutableTrait;

  /**
   * @param SQLQuery $query
   * @param array $assignmentList
   */
  public function __construct(
    protected readonly SQLQuery $query,
    protected readonly array $assignmentList
  )
  {
    $queryString = 'SET ';
    $separator = ', ';
    foreach ($assignmentList as $key => $value)
    {
      $identifier = $this->query->quoteIdentifier((string)$key);

      if (in_array($key, $this->query->passwordHashFields(), true))
      {
        $value = password_hash($value, $this->query->passwordHashAlgorithm());
      }
      if (is_string($value) && $value === 'CURRENT_TIMESTAMP') {
        $queryString .= $identifier . "={$value}{$separator}";
        continue;
      }

      $queryString .= $identifier . '=' . $this->query->addParam($value) . $separator;
    }
    $queryString = trim($queryString, $separator);
    $this->query->appendQueryString( tail: $queryString );
  }

  /**
   * @param string|array|FindOptions|FindWhereOptions $condition
   * @return SQLWhereClause
   */
  public function where(string|array|FindOptions|FindWhereOptions $condition): SQLWhereClause
  {
    return $this->createWhereClause(condition: $condition);
  }

  /**
   * Create the WHERE-clause builder used by this assignment list.
   *
   * Dialect-specific subclasses override this method to keep the fluent
   * chain on their own typed WHERE builders.
   *
   * @param string|array|FindOptions|FindWhereOptions $condition The condition to compile and append.
   * @return SQLWhereClause Returns the WHERE-clause builder.
   */
  protected function createWhereClause(string|array|FindOptions|FindWhereOptions $condition): SQLWhereClause
  {
    return new SQLWhereClause(query: $this->query, condition: $condition);
  }
}
