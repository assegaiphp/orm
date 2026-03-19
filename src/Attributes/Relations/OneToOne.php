<?php

namespace Assegai\Orm\Attributes\Relations;

use Assegai\Orm\Exceptions\ClassNotFoundException;
use Assegai\Orm\Relations\RelationOptions;
use Attribute;

/**
 * One-to-one is a relation where A contains only one instance of B and B
 * contains only one instance of A. For example, if we had `User` and
 * `Profile` entities, `User` can have only a single `Profile`, while a
 * single `Profile` is owned by one `User`.
 *
 * Example:
 * ```php
 * #[OneToOne(ProfileEntity::class)]
 * #[JoinColumn('profile_id')]
 * public ?ProfileEntity $profile = null;
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class OneToOne
{
  /**
   * OneToOne constructor.
   *
   * @param class-string $type The entity class this property points to.
   * Example: in `UserEntity::$profile`, use `ProfileEntity::class`.
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
