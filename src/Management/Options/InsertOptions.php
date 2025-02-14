<?php

namespace Assegai\Orm\Management\Options;

/**
 * Options for insert operations.
 *
 * @package Assegai\Orm\Management\Options
 */
readonly class InsertOptions
{
  /**
   * InsertOptions constructor.
   *
   * @param object|string[]|null $relations The relations to include.
   * @param bool $isDebug Whether to output debug information.
   */
  public function __construct(
    public object|array|null $relations = null,
    public bool $isDebug = false
  )
  {
  }
}