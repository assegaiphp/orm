<?php

namespace Assegai\Orm\Queries\QueryBuilder\Results;

class DeleteResult
{
  /**
   * @param mixed $raw Raw SQL result returned by executed query.
   * @param int|null $affected Number of affected rows/documents. Not all drivers support this.
   */
  public function __construct(
    public readonly mixed     $raw,
    public readonly ?int      $affected
  )
  {
  }
}