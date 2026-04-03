<?php

namespace Assegai\Orm\Results;

use Assegai\Orm\Queries\QueryBuilder\Results\QueryFindResult;

/**
 * Public-facing result object returned by ORM find operations.
 *
 * @template T
 * @template-extends QueryFindResult<T>
 */
readonly class FindResult extends QueryFindResult
{
}
