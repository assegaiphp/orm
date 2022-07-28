<?php

namespace Assegai\Orm\Queries\QueryBuilder\Results;

class InsertResult
{
  /**
   * @param object|null $identifiers Contains inserted entity id. Has entity-like structure (not just column database name and values).
   * @param mixed $raw Raw SQL result returned by executed query.
   * @param object|null $generatedMaps Generated values returned by a database. Has entity-like structure (not just column database name and values).
   */
  public function __construct(
    public readonly ?object $identifiers,
    public readonly mixed      $raw,
    public readonly ?object $generatedMaps,
  )
  {
  }
}