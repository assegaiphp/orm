<?php

namespace Assegai\Orm\Queries\QueryBuilder\Results;

use stdClass;

class InsertResult
{
  /**
   * @param stdClass|null $identifiers Contains inserted entity id. Has entity-like structure (not just column database name and values).
   * @param mixed $raw Raw SQL result returned by executed query.
   * @param stdClass|null $generatedMaps Generated values returned by a database. Has entity-like structure (not just column database name and values).
   */
  public function __construct(
    public readonly ?stdClass $identifiers,
    public readonly mixed      $raw,
    public readonly ?stdClass $generatedMaps,
  )
  {
  }
}