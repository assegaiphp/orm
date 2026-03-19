<?php

namespace Assegai\Orm\Attributes\Relations;

use Assegai\Orm\Exceptions\ClassNotFoundException;
use Assegai\Orm\Relations\RelationOptions;
use Attribute;

/**
 * A many-to-one relation means "many rows on this side belong to one row on the other side".
 *
 * Example:
 * many restaurants can belong to one organization.
 *
 * ```php
 * #[ManyToOne(OrganizationEntity::class)]
 * #[JoinColumn('organization_id')]
 * public ?OrganizationEntity $organization = null;
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ManyToOne
{
  /**
   * ManyToOne constructor.
   *
   * @param class-string $type The entity class this property points to.
   * Example: in `RestaurantEntity::$organization`, use `OrganizationEntity::class`.
   * @param string|null $name Optional custom relation name. Most applications can leave this as `null`.
   * @param string|null $alias Optional alias for custom query or mapping scenarios.
   * Most applications can leave this as `null`.
   * @param RelationOptions|null $options Extra relation behavior such as excluded fields.
   * @throws ClassNotFoundException
   */
  public function __construct(
    public readonly string $type,
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
