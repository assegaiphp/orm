<?php

namespace Assegai\Orm\Attributes\Relations;

use Assegai\Orm\Exceptions\ClassNotFoundException;
use Assegai\Orm\Relations\RelationOptions;
use Attribute;

/**
 * A one-to-many relation means "this entity has a collection of related entities".
 *
 * The foreign key lives on the related entity's ManyToOne side. In most cases the
 * inverse side is enough here; the referenced column is resolved from JoinColumn
 * metadata on the owning side.
 *
 * ```php
 * #[OneToMany(type: RestaurantEntity::class, inverseSide: 'organization')]
 * public array $restaurants = [];
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class OneToMany
{
  /**
   * @var string|null Legacy override for the local property to match against. New code should omit this.
   */
  public readonly ?string $referencedProperty;

  /**
   * @var string|null Property on the related entity that points back to this entity.
   */
  public readonly ?string $inverseSide;

  /**
   * OneToMany constructor.
   *
   * @param class-string $type The entity class for the items in this collection.
   * @param string|null $referencedProperty Legacy local property override. If $inverseSide is omitted,
   * this positional argument is treated as the inverse side for TypeORM-style usage.
   * @param string|null $inverseSide The property on the child entity that points back to this parent.
   * When omitted, Assegai will infer it if the target entity has exactly one ManyToOne back to this entity.
   * @param string|null $name Optional custom relation name. Most applications can leave this as `null`.
   * @param string|null $alias Optional alias for custom query or mapping scenarios.
   * @param RelationOptions|null $options Extra relation behavior such as excluded fields.
   * @throws ClassNotFoundException
   */
  public function __construct(
    public readonly string $type,
    ?string $referencedProperty = null,
    ?string $inverseSide = null,
    public readonly ?string $name = null,
    public readonly ?string $alias = null,
    public ?RelationOptions $options = null
  )
  {
    if ($inverseSide === null && $referencedProperty !== null) {
      $this->referencedProperty = null;
      $this->inverseSide = $referencedProperty;
    } else {
      $this->referencedProperty = $referencedProperty;
      $this->inverseSide = $inverseSide;
    }

    if (is_null($this->options)) {
      $this->options = new RelationOptions();
    }

    if (!class_exists($type)) {
      throw new ClassNotFoundException(className: $type);
    }
  }
}
