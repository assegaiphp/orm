<?php

namespace Assegai\Orm\Attributes\Relations;

use Assegai\Orm\Relations\RelationOptions;
use Attribute;

/**
 * Many-to-many is a type of relationship when Entity1 can have multiple instances of Entity2, and Entity2 can have
 * multiple instances of Entity1. To achieve it, this type of relation creates a junction table, where it storage
 * entity1 and entity2 ids. This is owner side of the relationship.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class ManyToMany
{
  /**
   * ManyToMany constructor.
   *
   * @param class-string $type The class name of the entity
   * @param class-string|null $inverseSide The class name of the inverse side of the relationship
   * @param RelationOptions|null $options
   */
  public function __construct(
    public string           $type,
    public ?string          $inverseSide = null,
    public ?RelationOptions $options = null,
  )
  {
  }
}