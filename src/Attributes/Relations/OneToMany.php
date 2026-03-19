<?php

namespace Assegai\Orm\Attributes\Relations;

use Assegai\Orm\Exceptions\ClassNotFoundException;
use Assegai\Orm\Relations\RelationOptions;
use Attribute;

/**
 * A one-to-many relation means "this entity owns a list of those entities".
 *
 * Example:
 * `OrganizationEntity` can have many `RestaurantEntity` records.
 *
 * ```php
 * #[OneToMany(RestaurantEntity::class, 'id', 'organization')]
 * public ?array $restaurants = null;
 * ```
 *
 * Read that example like this:
 * - `RestaurantEntity::class`: each item in the list is a restaurant
 * - `'id'`: use the organization's `id` when matching child rows
 * - `'organization'`: each restaurant points back through its `organization` property
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class OneToMany
{
  /**
   * OneToMany constructor.
   *
   * @param class-string $type The entity class for the items in this collection.
   * Example: if this property stores restaurants, use `RestaurantEntity::class`.
   * @param string $referencedProperty The property on the current entity that Assegai should use
   * when looking for children. In plain terms: "which value from this parent object should be matched
   * against the child rows?" Most of the time this is simply `'id'`.
   * Example: `#[OneToMany(RestaurantEntity::class, 'id', 'organization')]`
   * means "take `OrganizationEntity::$id` and use it to find matching restaurants".
   * @param string $inverseSide The property on the child entity that points back to this parent.
   * Example: if `RestaurantEntity` has `public ?OrganizationEntity $organization = null;`
   * then the inverse side is `'organization'`.
   * @param string|null $name Optional custom relation name. Most applications can leave this as `null`.
   * @param string|null $alias Optional alias for custom query or mapping scenarios.
   * Most applications can leave this as `null`.
   * @param RelationOptions|null $options Extra relation behavior such as excluded fields.
   * @throws ClassNotFoundException
   */
  public function __construct(
    public readonly string $type,
    public readonly string $referencedProperty,
    public readonly string $inverseSide,
    public readonly ?string $name = null,
    public readonly ?string $alias = null,
    public ?RelationOptions $options = null
  )
  {
    if (is_null($this->options)) {
      $this->options = new RelationOptions();
    }

    if (!class_exists($type)) {
      throw new ClassNotFoundException(className: $type);
    }
  }
}
