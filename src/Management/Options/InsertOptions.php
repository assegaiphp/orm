<?php

namespace Assegai\Orm\Management\Options;

/**
 * Options for insert operations.
 */
readonly class InsertOptions
{
  /**
   * InsertOptions constructor.
   *
   * @param object|array|null $relations
   */
  public function __construct(
    public object|array|null $relations = null,
  )
  {
  }
}