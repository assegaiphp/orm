<?php

namespace Assegai\Orm\Management;

use Assegai\Orm\Util\KeyBoolPair;
use stdClass;

/**
 * Specifies which relations need to be loaded with the main entity. (Shorthand for join and leftJoinAndSelect)
 */
final class FindRelationsOptions
{
  /**
   * @param stdClass | array<KeyBoolPair> $relations
   * @param array $exclude
   */
  public function __construct(
    public readonly object|array $relations,
    public readonly array $exclude = ['password'],
  )
  {
  }
}