<?php

namespace Assegai\Orm\Management\Options;

use Assegai\Orm\Exceptions\ORMException;
use JsonSerializable;

/**
 * Defines the search criteria for finding entities.
 */
class FindOptions implements JsonSerializable
{
  /**
   * The default value for withRealTotal.
   */
  const DEFAULT_WITH_REAL_TOTAL = true;

  /**
   * @param object|array|null $select The fields to select.
   * @param object|array|null $relations The relations to include.
   * @param FindWhereOptions|array|null $where The search criteria.
   * @param object|array|null $order The order of the results.
   * @param int|null $skip The number of results to skip.
   * @param int|null $limit The number of results to return.
   * @param array $exclude The fields to exclude.
   * @param bool $withRealTotal Whether to get the real total.
   */
  public function __construct(
    public readonly null|object|array $select = null,
    public readonly null|object|array $relations = null,
    public readonly null|FindWhereOptions|array $where = null,
    public readonly null|object|array $order = null,
    public readonly ?int $skip = null,
    public readonly ?int $limit = null,
    public readonly array $exclude = ['password'],
    public readonly bool $withRealTotal = self::DEFAULT_WITH_REAL_TOTAL,
    public readonly bool $isDebug = false
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
   * Creates a FindOptions instance from an array.
   *
   * @param array{select: array|null|object, relations: array|null|object, where: array|FindWhereOptions|null, order: array|null|object, skip: int|null, limit: int|null, exclude: array|string[], with_real_total: bool} $options The array of options.
   * @return FindOptions
   * @throws ORMException
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
    $withRealTotal = $options['with_real_total'] ?? self::DEFAULT_WITH_REAL_TOTAL;

    if (is_array($where)) {
      $where = new FindWhereOptions($where);
    }

    return new FindOptions(
      select: $select,
      relations: $relations,
      where: $where,
      order: $order,
      skip: $skip,
      limit: $limit,
      exclude: $exclude,
      withRealTotal: $withRealTotal
    );
  }

  /**
   * Converts a FindOptions instance to an array.
   *
   * @param FindOptions $options
   * @return array{select: array|null|object, relations: array|null|object, where: array|FindWhereOptions|null, order: array|null|object, skip: int|null, limit: int|null, exclude: array|string[], with_real_total: bool}
   */
  public static function toArray(self $options): array
  {
    return [
      'select' => $options->select,
      'relations' => $options->relations,
      'where' => $options->where,
      'order' => $options->order,
      'skip' => $options->skip,
      'limit' => $options->limit,
      'exclude' => $options->exclude,
      'with_real_total' => $options->withRealTotal,
    ];
  }

  /**
   * @inheritDoc
   */
  public function jsonSerialize(): array
  {
    return self::toArray($this);
  }
}