<?php

namespace Assegai\Orm\Management\Options;

use JetBrains\PhpStorm\ArrayShape;

/**
 * Defines the search criteria for finding entities.
 */
class FindOptions
{
  /**
   * @param object|array|null $select
   * @param object|array|null $relations
   * @param FindWhereOptions|array|null $where
   * @param object|array|null $order
   * @param int|null $skip
   * @param int|null $limit
   * @param array $exclude
   */
  public function __construct(
    public readonly null|object|array $select = null,
    public readonly null|object|array $relations = null,
    public readonly null|FindWhereOptions|array $where = null,
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
    $output = match(true) {
      is_array($this->where) => (function() {
        $where = '';
        foreach ($this->where as $key => $value)
        {
          $where .= "$key = $value";
        }
        return $where;
      })(),
      $this->where instanceof FindWhereOptions => strval($this->where),
      default => '',
    };

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

  /**
   * @param array $options
   * @return FindOptions
   */
  public static function fromArray(array $options): FindOptions
  {
    $select = $options['select'] ?? null;
    $relations = $options['relations'] ?? null;
    $where = $options['where'] ?? null;
    $order = $options['order'] ?? null;
    $skip = $options['skip'] ?? null;
    $limit = $options['limit'] ?? null;
    $exclude = $options['exclude'] ?? ['password'];

    if (is_array($where))
    {
      $where = new FindWhereOptions($where);
    }

    return new FindOptions(
      select: $select,
      relations: $relations,
      where: $where,
      order: $order,
      skip: $skip,
      limit: $limit,
      exclude: $exclude
    );
  }

  #[ArrayShape([
    'select' => "array|null|object",
    'relations' => "array|null|object",
    'where' => "array|\Assegai\Orm\Management\FindWhereOptions|null",
    'order' => "array|null|object",
    'skip' => "int|null",
    'limit' => "int|null",
    'exclude' => "array|string[]"
  ])]
  public static function toArray(self $options): array
  {
    return [
      'select' => $options->select,
      'relations' => $options->relations,
      'where' => $options->where,
      'order' => $options->order,
      'skip' => $options->skip,
      'limit' => $options->limit,
      'exclude' => $options->exclude
    ];
  }
}