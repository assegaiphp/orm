<?php

namespace Assegai\Orm\Queries\QueryBuilder\Results;

use Assegai\Orm\Interfaces\QueryResultInterface;
use Assegai\Orm\Traits\ResultErrorIntrospectorTrait;
use Throwable;
use Traversable;

/**
 * Class FindResult represents a result of a find query.
 * @package Assegai\Orm\Queries\QueryBuilder\Results
 *
 * @template T
 * @template-implements QueryResultInterface<T>
 */
readonly class FindResult implements QueryResultInterface
{
  use ResultErrorIntrospectorTrait;

  /**
   * @param mixed $raw Raw SQL result returned by executed query.
   * @param mixed $data The result data.
   * @param Throwable[] $errors List of errors.
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
   * @return Throwable[] List of errors.
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
   * Returns the first record from the result set.
   *
   * @return mixed<T> The first record, or null if the result set is empty.
   */
  public function getFirst(): mixed
  {
    if (is_array($this->data) && count($this->data) > 0) {
      return $this->data[0];
    }

    if (is_countable($this->data) || $this->data instanceof Traversable) {
      foreach ($this->data as $item) {
        return $item;
      }
    }

    return null;
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