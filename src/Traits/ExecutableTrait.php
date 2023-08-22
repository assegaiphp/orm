<?php

namespace Assegai\Orm\Traits;

use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Queries\Sql\SQLQueryResult;

/**
 * Trait ExecutableTrait. Provides the execute method for queries.
 *
 * @package Assegai\Orm\Traits
 */
trait ExecutableTrait
{
  /**
   * Executes the query and returns the result
   *
   * @return SQLQueryResult The result of the query.
   * @throws ORMException If the query fails to execute.
   */
  public function execute(): SQLQueryResult
  {
    return $this->query->execute();
  }

  /**
   * Exits the script and prints the query.
   *
   * @return never
   */
  public function debug(): never
  {
    $this->query->debug();
  }
}