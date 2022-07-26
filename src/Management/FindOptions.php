<?php

namespace Assegai\Orm\Management;

/**
 *
 */
class FindOptions
{
  /**
   * @param object|array|null $select
   * @param object|array|null $relations
   * @param FindWhereOptions|null $where
   * @param object|array|null $order
   * @param int|null $skip
   * @param int|null $limit
   * @param array $exclude
   */
  public function __construct(
    public readonly null|object|array $select = null,
    public readonly null|object|array $relations = null,
    public readonly ?FindWhereOptions $where = null,
    public readonly null|object|array $order = null,
    public readonly ?int $skip = null,
    public readonly ?int $limit = null,
    public readonly array $exclude = ['password'],
  ) { }

  /**
   * @return string
   */
  public function __toString(): string
  {
    $output = strval($this->where);

    if (!empty($limit))
    {
      $output .= " LIMIT $limit";

      if (!empty($skip))
      {
        $output .= " OFFSET $skip";
      }
    }

    return trim($output);
  }
}