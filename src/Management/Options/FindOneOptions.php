<?php

namespace Assegai\Orm\Management\Options;

use Assegai\Orm\Exceptions\ORMException;

/**
 * Defines the search criteria for finding a specific entity.
 *
 * @package Assegai\Orm\Management\Options
 */
class FindOneOptions extends FindOptions
{
  /**
   * @var int|null The maximum number of entities to return.
   */
  public readonly ?int $limit;
  /**
   * @var int|null The number of entities to skip.
   */
  public readonly ?int $skip;

  /**
   * Constructs a `FindOneOptions` object
   *
   * @param object|array|null $select Specifies what columns should be retrieved.
   * @param object|array|null $relations Indicates what relations of entity should be loaded (simplified left join form).
   * @param FindWhereOptions|array|null $where Simple condition that should be applied to match entities.
   * @param object|array|null $order Order, in which entities should be ordered.
   * @param array<string, string>|JoinOptions|null $join Join options.
   * @param string[] $exclude
   */
  public function __construct(
    null|object|array $select = null,
    null|object|array $relations = null,
    null|FindWhereOptions|array $where = null,
    null|object|array $order = null,
    public readonly null|array|JoinOptions $join = null,
    array $exclude = ['password'],
  )
  {
    parent::__construct(
      select: $select,
      relations: $relations,
      where: $where,
      order: $order,
      skip: 0,
      limit: 1,
      exclude: $exclude,
      withRealTotal: false
    );
  }

  /**
   * @param array{select: array|null|object, relations: array|null|object, where: array|FindWhereOptions|null, order: array|null|object, skip: int|null, limit: int|null, join: array<string, string>|JoinOptions|null, exclude: array|string[], with_real_total: bool} $options
   * @return FindOptions
   * @throws ORMException
   */
  public static function fromArray(array $options): FindOptions
  {
    $select = $options['select'] ?? null;
    $relations = $options['relations'] ?? null;
    $where = $options['where'] ?? null;
    $order = $options['order'] ?? null;
    $join = $options['join'] ?? null;
    $exclude = $options['exclude'] ?? ['password'];

    if (is_array($where)) {
      $where = new FindWhereOptions($where, $exclude);
    }

    return new FindOneOptions(
      select: $select,
      relations: $relations,
      where: $where,
      order: $order,
      join: $join,
      exclude: $exclude,
    );
  }
}
