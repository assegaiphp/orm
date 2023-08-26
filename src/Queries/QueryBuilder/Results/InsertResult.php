<?php

namespace Assegai\Orm\Queries\QueryBuilder\Results;

use Assegai\Orm\Interfaces\QueryResultInterface;

/**
 * Class InsertResult represents a result of an insert query.
 * @package Assegai\Orm\Queries\QueryBuilder\Results
 *
 * @template T
 * @template-implements QueryResultInterface<T>
 */
readonly class InsertResult implements QueryResultInterface
{
  /**
   * @param object|null $identifiers Contains inserted entity id. Has entity-like structure (not just column database name and values).
   * @param mixed $raw Raw SQL result returned by executed query.
   * @param object|null $generatedMaps Generated values returned by a database. Has entity-like structure (not just column database name and values).
   * @param array $errors List of errors.
   */
  public function __construct(
    public ?object $identifiers,
    public mixed   $raw,
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
   * Returns the inserted entity id. Has entity-like structure (not just column database name and values).
   *
   * @return null|object The inserted entity id. Has entity-like structure (not just column database name and values).
   */
  public function getData(): ?object
  {
    return $this->identifiers;
  }

  /**
   * @inheritDoc
   */
  public function getRaw(): mixed
  {
    return $this->raw;
  }
}