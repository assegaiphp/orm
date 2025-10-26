<?php

namespace Assegai\Orm\Queries\QueryBuilder\Results;

use Assegai\Orm\Interfaces\QueryResultInterface;
use Assegai\Orm\Traits\ResultErrorIntrospectorTrait;

/**
 * DeleteResult class. Represents result of delete query execution.
 * @package Assegai\Orm\Queries\QueryBuilder\Results
 *
 * @template T
 * @template-implements QueryResultInterface<T>
 */
readonly class DeleteResult implements QueryResultInterface
{
  use ResultErrorIntrospectorTrait;

  /**
   * Construct a new DeleteResult instance.
   *
   * @param mixed $raw Raw SQL result returned by executed query.
   * @param int|null $affected Number of affected rows/documents. Not all drivers support this.
   */
  public function __construct(
    public mixed $raw,
    public ?int  $affected,
    protected array $errors = []
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
   * Returns the number of affected rows/documents. Not all drivers support this.
   *
   * @return int|null Number of affected rows/documents. Not all drivers support this.
   */
  public function getData(): ?int
  {
    return $this->affected;
  }

  /**
   * @inheritDoc
   */
  public function getRaw(): mixed
  {
    return $this->raw;
  }

  /**
   * @inheritDoc
   */
  public function getTotalAffectedRows(): int
  {
    return $this->affected ?? -1;
  }
}