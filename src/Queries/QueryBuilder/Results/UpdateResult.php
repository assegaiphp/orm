<?php

namespace Assegai\Orm\Queries\QueryBuilder\Results;

use Assegai\Orm\Interfaces\QueryResultInterface;
use Assegai\Orm\Traits\ResultErrorIntrospectorTrait;

/**
 * UpdateResult class. Represents result of update query execution.
 * @package Assegai\Orm\Queries\QueryBuilder\Results
 *
 * @template T of object
 * @template-implements QueryResultInterface<T>
 * @property-read T|null $identifiers
 * @property-read T|null $generatedMaps
 */
readonly class UpdateResult implements QueryResultInterface
{
  use ResultErrorIntrospectorTrait;

  /**
   * @param mixed $raw Raw SQL result returned by executed query.
   * @param int|null $affected Number of affected rows/documents. Not all drivers support this.
   * @param T|null $identifiers Contains updated entity identifiers in an entity-like shape.
   * @param T|null $generatedMaps Generated values returned by the database in an entity-like shape.
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
   * Returns generated values returned by a database, identifiers, or the affected row count.
   *
   * @return T|int|null
   */
  public function getData(): null|object|int
  {
    return $this->generatedMaps ?? $this->identifiers ?? $this->affected;
  }

  /**
   * Returns the updated entity identifiers in an entity-like shape.
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
    return $this->affected ?? -1;
  }
}