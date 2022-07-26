<?php

namespace Assegai\Orm\Management;

use stdClass;

/**
 * Defines the special criteria to find a specific entity.
 */
class FindOneOptions extends FindOptions
{
  /**
   * Constructs a `FindOneOptions` object
   *
   * @param object|array|null $select Specifies what columns should be retrieved.
   * @param object|array|null $relations Indicates what relations of entity should be loaded (simplified left join form).
   * @param null|FindWhereOptions $where Simple condition that should be applied to match entities.
   * @param object|array|null $order Order, in which entities should be ordered.
   * @param null|int $skip Skips/offsets the specified number of entities.
   * @param null|int $limit Specifies the number of entities to return.
   * @param array|JoinOptions|null $join Specifies what relations should be loaded.
   * @param array $exclude
   * @noinspection PhpMissingParentConstructorInspection
   */
  public function __construct(
    public readonly null|object|array $select = null,
    public readonly null|object|array $relations = null,
    public readonly ?FindWhereOptions $where = null,
    public readonly null|object|array $order = null,
    public readonly ?int $skip = null,
    public readonly ?int $limit = null,
    public readonly null|array|JoinOptions $join = null,
    public readonly array $exclude = ['password'],
  ) { }
}
