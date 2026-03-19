<?php

namespace Assegai\Orm\Attributes\Relations;

use Assegai\Orm\Exceptions\ClassNotFoundException;
use Assegai\Orm\Relations\RelationOptions;
use Attribute;

/**
 * Many-to-many is a type of relationship when Entity1 can have multiple instances of Entity2, and Entity2 can have
 * multiple instances of Entity1. To achieve it, this type of relation creates a junction table, where it storage
 * entity1 and entity2 ids. This is owner side of the relationship.
 *
 * Example:
 * ```php
 * #[ManyToMany(UserEntity::class, inverseSide: 'organizations')]
 * #[JoinTable(name: 'organization_users', joinColumn: 'organization_id', inverseJoinColumn: 'user_id')]
 * public ?array $users = null;
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ManyToMany
{
  /**
   * ManyToMany constructor.
   *
   * @param class-string $type The entity class for the items in this collection.
   * Example: if `OrganizationEntity::$users` stores users, use `UserEntity::class`.
   * @param string|null $inverseSide The property on the other entity that points back to this relation.
   * Example: if `UserEntity` has `public ?array $organizations = null;` then the inverse side here is
   * `'organizations'`.
   * @param RelationOptions|null $options Extra relation behavior such as excluded fields.
   * @throws ClassNotFoundException
   */
  public function __construct(
    public string           $type,
    public ?string          $inverseSide = null,
    public ?RelationOptions $options = null,
  )
  {
    $this->options ??= new RelationOptions();

    if (!class_exists($type)) {
      throw new ClassNotFoundException(className: $type);
    }
  }
}
