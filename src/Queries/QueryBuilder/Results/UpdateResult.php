<?php

namespace Assegai\Orm\Queries\QueryBuilder\Results;

use Assegai\Orm\Interfaces\QueryResultInterface;
use Assegai\Orm\Traits\ResultErrorIntrospectorTrait;

/**
 * UpdateResult class. Represents result of update query execution.
 * @package Assegai\Orm\Queries\QueryBuilder\Results
 *
 * @template T
 * @template-implements QueryResultInterface<T>
 */
readonly class UpdateResult implements QueryResultInterface
{
  use ResultErrorIntrospectorTrait;

  /**
   * @param mixed $raw Raw SQL result returned by executed query.
   * @param int|null $affected Number of affected rows/documents. Not all drivers support this.
   * @param object|null $identifiers Contains inserted entity id. Has entity-like structure (not just column database
   * name and values).
   * @param object|null $generatedMaps Generated values returned by a database. Has entity-like structure (not just
   * column database name and values).
   */
  public function __construct(
    public mixed   $raw,
    public ?int    $affected,
    public ?object $identifiers,
    public ?object $generatedMaps,
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
   * Generated values returned by a database. Has entity-like structure (not just column database name and values).
   *
   * @return object|int|null Generated values returned by a database or inserted entity id or number of affected
   * rows/documents.
   */
  public function getData(): null|object|int
  {
    return $this->generatedMaps ?? $this->identifiers ?? $this->affected;
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
    return $this->affected;
  }
}