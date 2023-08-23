<?php

namespace Assegai\Orm\Queries\QueryBuilder\Results;

use Assegai\Orm\Interfaces\QueryResultInterface;

/**
 * DeleteResult class. Represents result of delete query execution.
 */
readonly class DeleteResult implements QueryResultInterface
{
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
}