<?php

namespace Assegai\Orm\Metadata;

use Assegai\Orm\Enumerations\RelationType;
use Assegai\Orm\Relations\RelationOptions;
use Closure;

class RelationMetadataArgs
{
  /**
   * @param Closure|string $target
   * @param string $propertyName
   * @param bool $isLazy
   * @param RelationType $relationType
   * @param RelationOptions $options
   * @param bool|null $isTreeParent
   * @param bool|null $isTreeChildren
   * @param Closure|null $inverseSideProperty
   */
  public function __construct(
    public readonly Closure|string $target,
    public readonly string $propertyName,
    public readonly bool $isLazy,
    public readonly RelationType $relationType,
    public readonly RelationOptions $options,
    public readonly ?bool $isTreeParent = null,
    public readonly ?bool $isTreeChildren = null,
    public readonly ?Closure $inverseSideProperty = null
  )
  {
  }
}