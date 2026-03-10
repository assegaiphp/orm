<?php

namespace Assegai\Orm\Management\Options;

/**
 * Options for insert operations.
 *
 * @package Assegai\Orm\Management\Options
 */
readonly class InsertOptions
{
  public object|array|null $relations;
  /**
   * InsertOptions constructor.
   *
   * @param object|string[]|null $relations The relations to include.
   * @param bool $isDebug Whether to output debug information.
   */
  public function __construct(
    object|array|null $relations = null,
    public bool              $isDebug = false,
    public ?array            $readonlyColumns = null,
    public string            $primaryKeyField = 'id',
  )
  {
    $this->relations = array_map(fn($relation) => (is_string($relation) ? trim($relation) : throw new \InvalidArgumentException("Each relation must be of type string")), $relations ?? []);
  }
}