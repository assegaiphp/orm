<?php

namespace Assegai\Orm\Metadata;

use Closure;

/**
 * Arguments for the RelationCountMetadata class.
 */
class RelationCountMetadataArgs
{
  /**
   * @param Closure|string $target Class to which this attribute is applied.
   * @param string $propertyName Class's property name to which this attribute is applied.
   * @param string $relation Target's relation which it should count.
   * @param string|null $alias Alias of the joined (destination) table.
   * @param Closure|null $queryBuilderFactor Extra condition applied to "ON" section of join.
   */
  public function __construct(
    public readonly Closure|string $target,
    public readonly string $propertyName,
    public readonly string $relation,
    public readonly ?string $alias,
    public readonly ?Closure $queryBuilderFactor,
  )
  {
  }
}