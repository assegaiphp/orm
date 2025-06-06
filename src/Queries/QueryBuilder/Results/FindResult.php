<?php

namespace Assegai\Orm\Queries\QueryBuilder\Results;

use Assegai\Orm\Interfaces\QueryResultInterface;

/**
 * Class FindResult represents a result of a find query.
 * @package Assegai\Orm\Queries\QueryBuilder\Results
 *
 * @template T
 * @template-implements QueryResultInterface<T>
 */
readonly class FindResult implements QueryResultInterface
{
  /**
   * @param mixed $raw Raw SQL result returned by executed query.
   * @param mixed $data The result data.
   * @param array $errors List of errors.
   */
  public function __construct(
    protected mixed $raw,
    protected mixed $data,
    protected array $errors = [],
    protected int $affected = 0,
    protected ?int $total = null
  )
  {
  }

  /**
   * @inheritDoc
   */
  public function isOk(): bool
  {
    return empty($this->errors);
  }

  /**
   * @inheritDoc
   */
  public function isError(): bool
  {
    return !$this->isOk();
  }

  /**
   * @inheritDoc
   */
  public function getErrors(): array
  {
    return $this->errors;
  }

  /**
   * @inheritDoc
   */
  public function getData(): mixed
  {
    return $this->data;
  }

  /**
   * @inheritDoc
   */
  public function getRaw(): mixed
  {
    return $this->raw;
  }

  /**
   * Returns the total number of records.
   *
   * @return int The total number of records.
   */
  public function getTotal(): int
  {
    if ($this->total !== null) {
      return $this->total;
    }

    if (is_countable($this->data)) {
      return count($this->data);
    }

    return 0;
  }

  /**
   * Returns the total number of affected rows.
   *
   * @return int The total number of affected rows.
   */
  public function getTotalAffectedRows(): int
  {
    return $this->affected;
  }

  /**
   * Checks if the result is empty.
   *
   * @return bool True if the result is empty, false otherwise.
   */
  public function isEmpty(): bool
  {
    return $this->getTotal() === 0;
  }
}