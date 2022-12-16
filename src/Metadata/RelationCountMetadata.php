<?php

namespace Assegai\Orm\Metadata;

use Closure;

/**
 * Contains all information about an entity's relation count.
 */
class RelationCountMetadata
{
  public readonly RelationMetadata $relation;
  public readonly Closure|string $relationNameOrFactory;
  public readonly Closure|string $target;
  public readonly string $propertyName;
  public readonly ?string $alias;
  public readonly ?Closure $queryBuilderFactory;

  public function __construct(
    public readonly EntityMetadata $entityMetadata,
    public readonly RelationCountMetadataArgs $args
  )
  {
    $this->target = $this->args->target;
    $this->propertyName = $this->args->propertyName;
    $this->relationNameOrFactory = $this->args->relation;
    $this->alias = $this->args->alias;
    $this->queryBuilderFactory = $this->args->queryBuilderFactor;
  }

  public function build(): void
  {
    $propertyPath = $this->relationNameOrFactory instanceof Closure ? $this->relationNameOrFactory($this->entityMetadata->propertiesMap) : $this->relationNameOrFactory;

  }
}