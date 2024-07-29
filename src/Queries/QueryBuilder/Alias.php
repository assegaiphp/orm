<?php

namespace Assegai\Orm\Queries\QueryBuilder;

use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Metadata\EntityMetadata;
use Assegai\Orm\Queries\QueryBuilder\Enumerations\AliasType;
use Closure;

class Alias
{
  /**
   * Alias constructor.
   *
   * @param AliasType $type The type of the alias.
   * @param string $name The name of the alias.
   * @param string|null $tablePath The table for which the alias is applied. Used only for aliases which select custom tables.
   * @param string|null $subQuery Set if this alias is a sub query.
   * @param EntityMetadata|null $metadata The metadata of the alias.
   */
  public function __construct(
    protected AliasType $type,
    protected string $name,
    protected ?string $tablePath = null,
    protected ?string $subQuery = null,
    protected ?EntityMetadata $metadata = null,
  )
  {
  }

  /**
   * Gets the type of the alias.
   *
   * @return AliasType The type of the alias.
   */
  public function getAliasType(): AliasType
  {
    return $this->type;
  }

  /**
   * Sets the type of the alias.
   *
   * @param AliasType $type The type of the alias.
   * @return void
   */
  public function setAliasType(AliasType $type): void
  {
    $this->type = $type;
  }

  /**
   * Gets the name of the alias.
   *
   * @return string The name of the alias.
   */
  public function getName(): string
  {
    return $this->name;
  }

  /**
   * Sets the name of the alias.
   *
   * @param string $name The name of the alias.
   * @return void
   */
  public function setName(string $name): void
  {
    $this->name = $name;
  }

  /**
   * Gets the table path of the alias.
   *
   * @return string|null The table path of the alias.
   */
  public function getTablePath(): ?string
  {
    return $this->tablePath;
  }

  /**
   * Sets the table path of the alias.
   *
   * @param string|null $tablePath The table path of the alias.
   * @return void
   */
  public function setTablePath(?string $tablePath): void
  {
    $this->tablePath = $tablePath;
  }

  /**
   * Gets the sub query of the alias.
   *
   * @return string|null The sub query of the alias.
   */
  public function getSubQuery(): ?string
  {
    return $this->subQuery;
  }

  /**
   * Sets the sub query of the alias.
   *
   * @param string|null $subQuery The sub query of the alias.
   * @return void
   */
  public function setSubQuery(?string $subQuery): void
  {
    $this->subQuery = $subQuery;
  }

  /**
   * Gets the target of the alias.
   *
   * @return string|callable|Closure The target of the alias.
   */
  public function getTarget(): string | callable | Closure
  {
    return $this->metadata?->target ?? '';
  }

  /**
   * Determines if the alias has metadata.
   *
   * @return bool True if the alias has metadata; otherwise, false.
   */
  public function hasMetaData(): bool
  {
    return $this->metadata !== null;
  }

  /**
   * Determines if the alias does not have metadata.
   *
   * @return bool True if the alias does not have metadata; otherwise, false.
   */
  public function doesntHaveMetaData(): bool
  {
    return ! $this->hasMetaData();
  }

  /**
   * Gets the metadata of the alias.
   *
   * @param EntityMetadata $metadata
   * @return void
   */
  public function setMetaData(EntityMetadata $metadata): void
  {
    $this->metadata = $metadata;
  }

  /**
   * Gets the metadata of the alias.
   *
   * @return EntityMetadata The metadata of the alias.
   * @throws ORMException
   */
  public function getMetaData(): EntityMetadata
  {
    if ( $this->doesntHaveMetaData() ) {
      throw new ORMException("Cannot get meta data for the given alias $this->name.");
    }

    return $this->metadata;
  }
}