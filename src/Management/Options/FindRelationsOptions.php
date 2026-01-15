<?php

namespace Assegai\Orm\Management\Options;

use Assegai\Orm\Util\KeyBoolPair;
use InvalidArgumentException;
use stdClass;

/**
 * Specifies which relations need to be loaded with the main entity. (Shorthand for join and leftJoinAndSelect)
 *
 * @package Assegai\Orm\Management\Options
 */
final readonly class FindRelationsOptions
{
  public object|array $relations;

  /**
   * @param stdClass | array<KeyBoolPair> $relations
   * @param string[] $exclude
   */
  public function __construct(object|array $relations, public array $exclude = ['password'])
  {
    $this->relations = array_map(fn($relation) => (is_string($relation) ? trim($relation) : throw new InvalidArgumentException("Each relation must be of type string")), $relations);
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