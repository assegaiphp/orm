<?php

namespace Assegai\Orm\Management\Options;

use Assegai\Orm\Util\KeyBoolPair;
use stdClass;

/**
 * Specifies which relations need to be loaded with the main entity. (Shorthand for join and leftJoinAndSelect)
 *
 * @package Assegai\Orm\Management\Options
 */
final class FindRelationsOptions
{
  /**
   * @param stdClass | array<KeyBoolPair> $relations
   * @param string[] $exclude
   */
  public function __construct(
    public readonly object|array $relations,
    public readonly array $exclude = ['password'],
  )
  {
  }

  /**
   * @param array{relations: ?array<KeyBoolPair>, exclude: ?string[]} $options
   * @return FindRelationsOptions
   */
  public static function fromArray(array $options): FindRelationsOptions
  {
    $relations = $options['relations'] ?? [];
    $exclude = $options['exclude'] ?? ['password'];

    return new FindRelationsOptions(relations: $relations, exclude: $exclude);
  }
}