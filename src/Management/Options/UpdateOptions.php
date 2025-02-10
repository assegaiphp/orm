<?php

namespace Assegai\Orm\Management\Options;

/**
 * Options for updating an entity.
 */
readonly class UpdateOptions
{
  /**
   * UpdateOptions constructor.
   *
   * @param object|array|null $relations
   */
  public function __construct(
    public object|array|null $relations = null,
  )
  {

  }
}