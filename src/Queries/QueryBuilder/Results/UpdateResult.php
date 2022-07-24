<?php

namespace Assegai\Orm\Queries\QueryBuilder\Results;

use stdClass;

class UpdateResult
{
  /**
   * @param mixed $raw Raw SQL result returned by executed query.
   * @param int|null $affected Number of affected rows/documents. Not all drivers support this.
   * @param stdClass|null $identifiers Contains inserted entity id. Has entity-like structure (not just column database name and values).
   * @param stdClass|null $generatedMaps Generated values returned by a database. Has entity-like structure (not just column database name and values).
   */
  public function __construct(
    public readonly mixed     $raw,
    public readonly ?int      $affected,
    public readonly ?stdClass $identifiers,
    public readonly ?stdClass $generatedMaps,
  )
  {
  }
}