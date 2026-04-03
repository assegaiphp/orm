<?php

namespace Assegai\Orm\Queries\QueryBuilder\Results;

use Assegai\Orm\Interfaces\QueryResultInterface;
use Assegai\Orm\Traits\ResultErrorIntrospectorTrait;

/**
 * Class InsertResult represents a result of an insert query.
 * @package Assegai\Orm\Queries\QueryBuilder\Results
 *
 * @template T of object
 * @template-implements QueryResultInterface<T>
 * @property-read T|null $identifiers
 * @property-read T|null $generatedMaps
 */
readonly class InsertResult implements QueryResultInterface
{
  use ResultErrorIntrospectorTrait;

  /**
   * @param T|null $identifiers Contains inserted entity identifiers in an entity-like shape.
   * @param mixed $raw Raw SQL result returned by executed query.
   * @param T|null $generatedMaps Generated values returned by the database in an entity-like shape.
   * @param array $errors List of errors.
   */
  public function __construct(
    public ?object $identifiers,
    public mixed   $raw,
    public ?object $generatedMaps,
    protected array $errors = [],
    protected int $affected = 0,
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
   * Returns the inserted entity identifiers in an entity-like shape.
   *
   * @return T|null
   */
  public function getData(): ?object
  {
    return $this->identifiers;
  }

  /**
   * Returns the inserted entity identifiers in an entity-like shape.
   *
   * @return T|null
   */
  public function getIdentifiers(): ?object
  {
    return $this->identifiers;
  }

  /**
   * Returns generated values returned by the database in an entity-like shape.
   *
   * @return T|null
   */
  public function getGeneratedMaps(): ?object
  {
    return $this->generatedMaps;
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