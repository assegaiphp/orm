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
   */
  public function __construct(public readonly stdClass|array $relations)
  {
  }
}